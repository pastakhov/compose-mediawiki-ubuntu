#!/bin/bash
# see https://github.com/mysql/mysql-docker
set -e

# if command starts with an option, prepend mysqld
if [ "${1:0:1}" = '-' ]; then
	set -- mysqld "$@"
fi

if [ "$1" = 'mysqld' ]; then
	# Test we're able to startup without errors. We redirect stdout to /dev/null so
	# only the error messages are left.
	result=0
	output=$("$@" --verbose --help 2>&1 > /dev/null) || result=$?
	if [ ! "$result" = "0" ]; then
		echo >&2 'error: could not run mysql. This could be caused by a misconfigured my.cnf'
		echo >&2 "$output"
		exit 1
	fi

	# Get config
	DATADIR="$("$@" --verbose --help --log-bin-index=/tmp/tmp.index 2>/dev/null | awk '$1 == "datadir" { print $2; exit }')"
	VERSION="$(mysql --version|awk '{ print $5 }'|awk -F\, '{ print $1 }')"

	if [ ! -d "$DATADIR/mysql" ]; then
		if [ -z "$MYSQL_ROOT_PASSWORD" -a -z "$MYSQL_ALLOW_EMPTY_PASSWORD" -a -z "$MYSQL_RANDOM_ROOT_PASSWORD" ]; then
			echo >&2 'error: database is uninitialized and password option is not specified '
			echo >&2 '  You need to specify one of MYSQL_ROOT_PASSWORD, MYSQL_ALLOW_EMPTY_PASSWORD and MYSQL_RANDOM_ROOT_PASSWORD'
			exit 1
		fi
		# If the password variable is a filename we use the contents of the file
		if [ -f "$MYSQL_ROOT_PASSWORD" ]; then
			MYSQL_ROOT_PASSWORD="$(cat $MYSQL_ROOT_PASSWORD)"
		fi
		mkdir -p "$DATADIR"
		chown -R mysql:mysql "$DATADIR"

		echo 'Initializing database'
		"$@" --initialize-insecure=on
		echo 'Database initialized'

		"$@" --skip-networking --socket=/var/run/mysqld/mysqld.sock &
		pid="$!"

		mysql=( mysql --protocol=socket -uroot -hlocalhost --socket=/var/run/mysqld/mysqld.sock)

		for i in {30..0}; do
			if echo 'SELECT 1' | "${mysql[@]}" &> /dev/null; then
				break
			fi
			echo 'MySQL init process in progress...'
			sleep 1
		done
		if [ "$i" = 0 ]; then
			echo >&2 'MySQL init process failed.'
			exit 1
		fi

		mysql_tzinfo_to_sql /usr/share/zoneinfo | "${mysql[@]}" mysql
		
		if [ ! -z "$MYSQL_RANDOM_ROOT_PASSWORD" ]; then
			MYSQL_ROOT_PASSWORD="$(pwmake 128)"
			echo "GENERATED ROOT PASSWORD: $MYSQL_ROOT_PASSWORD"
		fi
		if [ -z "$MYSQL_ROOT_HOST" ]; then
			ROOTCREATE="ALTER USER 'root'@'localhost' IDENTIFIED BY '${MYSQL_ROOT_PASSWORD}';"
		else
			ROOTCREATE="ALTER USER 'root'@'localhost' IDENTIFIED BY '${MYSQL_ROOT_PASSWORD}'; \
			CREATE USER 'root'@'${MYSQL_ROOT_HOST}' IDENTIFIED BY '${MYSQL_ROOT_PASSWORD}'; \
			GRANT ALL ON *.* TO 'root'@'${MYSQL_ROOT_HOST}' WITH GRANT OPTION ;"
		fi
		"${mysql[@]}" <<-EOSQL
			-- What's done in this file shouldn't be replicated
			--  or products like mysql-fabric won't work
			SET @@SESSION.SQL_LOG_BIN=0;
			DELETE FROM mysql.user WHERE user NOT IN ('mysql.sys', 'mysqlxsys', 'root') OR host NOT IN ('localhost');
			${ROOTCREATE}
			DROP DATABASE IF EXISTS test ;
			FLUSH PRIVILEGES ;
		EOSQL
		if [ ! -z "$MYSQL_ROOT_PASSWORD" ]; then
			mysql+=( -p"${MYSQL_ROOT_PASSWORD}" )
		fi

		if [ "$MYSQL_DATABASE" ]; then
			echo "CREATE DATABASE IF NOT EXISTS \`$MYSQL_DATABASE\` ;" | "${mysql[@]}"
			mysql+=( "$MYSQL_DATABASE" )
		fi

		if [ "$MYSQL_USER" -a "$MYSQL_PASSWORD" ]; then
			echo "CREATE USER '"$MYSQL_USER"'@'%' IDENTIFIED BY '"$MYSQL_PASSWORD"' ;" | "${mysql[@]}"

			if [ "$MYSQL_DATABASE" ]; then
				echo "GRANT ALL ON \`"$MYSQL_DATABASE"\`.* TO '"$MYSQL_USER"'@'%' ;" | "${mysql[@]}"
			fi

			echo 'FLUSH PRIVILEGES ;' | "${mysql[@]}"
		fi
		echo
		for f in /docker-entrypoint-initdb.d/*; do
			case "$f" in
				*.sh)  echo "$0: running $f"; . "$f" ;;
				*.sql) echo "$0: running $f"; "${mysql[@]}" < "$f" && echo ;;
				*)     echo "$0: ignoring $f" ;;
			esac
			echo
		done

		if [ ! -z "$MYSQL_ONETIME_PASSWORD" ]; then
			"${mysql[@]}" <<-EOSQL
				ALTER USER 'root'@'%' PASSWORD EXPIRE;
			EOSQL
		fi
		if ! kill -s TERM "$pid" || ! wait "$pid"; then
			echo >&2 'MySQL init process failed.'
			exit 1
		fi

		echo
		echo 'MySQL init process done. Ready for start up.'
		echo

		echo "$VERSION" > "$DATADIR/db_version"
		echo "Database version: $VERSION"
		echo

	else
		echo 'Check for the need to update the MySQL databases';
		if [ -f "$DATADIR/db_version" ]; then
			db_version="$(cat "$DATADIR/db_version" 2>/dev/null)"
		fi
		if [[ $db_version != $VERSION && -n $VERSION  ]]; then
			echo "Trying to run upgrade of MySQL databases..."
			"$@" --skip-networking --socket=/var/run/mysqld/mysqld.sock --skip-grant-tables &
			pid="$!"

			mysql=( mysql --protocol=socket -uroot -hlocalhost --socket=/var/run/mysqld/mysqld.sock)

			for i in {30..0}; do
				if echo 'SELECT 1' | "${mysql[@]}" &> /dev/null; then
					break
				fi
				echo 'MySQL upgrade databases process in progress...'
				sleep 1
			done
			if [ "$i" = 0 ]; then
				echo >&2 'MySQL upgrade databases process failed.'
				exit 1
			fi

			# Run upgrade itself
			echo "Running upgrade itself..."
			echo "It will do some chek first and report all errors and tries to correct them"
			echo
			if /usr/bin/mysql_upgrade --no-defaults --force --socket=/var/run/mysqld/mysqld.sock; then
				echo "Everything upgraded successfully"
				echo "$VERSION" > "$DATADIR/db_version"
				echo "Database version: $VERSION"
				echo
			else
				echo >&2 'MySQL upgrade databases failed.'
				exit 1
			fi
			
			if ! kill -s TERM "$pid" || ! wait "$pid"; then
				echo >&2 'MySQL upgrade databases process failed.'
				exit 1
			fi

			echo
			echo 'MySQL upgrade databases process done. Ready for start up.'
			echo
		else
			echo "MySQL databases do not need to be upgraded"
			echo
		fi
	fi

	chown -R mysql:mysql "$DATADIR"
fi

exec "$@"

