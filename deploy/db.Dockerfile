FROM mariadb:10.11

# MariaDB runs any *.sql / *.sh from /docker-entrypoint-initdb.d on first
# (empty-volume) boot, in lexical order. schema.sql creates tables, seed.sql
# loads roles/permissions/menu/coupon. Both are idempotent (IF NOT EXISTS /
# ON DUPLICATE KEY), so re-import is safe but only happens once per volume.
COPY database/schema.sql /docker-entrypoint-initdb.d/10-schema.sql
COPY database/seed.sql /docker-entrypoint-initdb.d/20-seed.sql