#echo .bash_aliases run
export WP_GIT_UPDATER_GIT_TOKEN='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'
export WP_GIT_UPDATER_TOKEN='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'
export EDITOR='/usr/bin/vi'
export SETUP=.bash_aliases
alias dblogin='mariadb -u $un -p $db'
alias splitSQL="sed 's/),(/),\n(/g' <wp_posts.sql >wp_posts.split.sql"
alias splitSQLposts="sed 's/),(/),\n(/g' <wp_posts.sql >wp_posts.split.sql"
alias splitSQLpages="sed 's/),(/),\n(/g' <wp_pages.sql >wp_pages.split.sql"
alias splitSQLcomments="sed 's/),(/),\n(/g' <wp_comments.sql >wp_comments.split.sql"
alias splitSQLcommentmeta="sed 's/),(/),\n(/g' <wp_comments.sql >wp_commentmeta.split.sql"
alias splitSQLusers="sed 's/),(/),\n(/g' <wp_users.sql >wp_users.split.sql"
alias splitSQLusermeta="sed 's/),(/),\n(/g' <wp_usermeta.sql >wp_usermeta.split.sql"
alias splitAll='splitSQLposts; splitSQLpages; splitSQLcomments; splitSQLcommentmeta; splitSQLusers; splitSQLusermeta;'

#echo .bash_aliases done
