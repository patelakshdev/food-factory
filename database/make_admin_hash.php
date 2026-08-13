<?php
/**
 * Usage: php database/make_admin_hash.php "YourStrongPassword123!"
 * Prints a password_hash() value to paste into seed.sql / users.password_hash.
 * Never commit real production credentials into source control.
 */
if ($argc < 2) {
    fwrite(STDERR, "Usage: php make_admin_hash.php <password>\n");
    exit(1);
}
echo password_hash($argv[1], PASSWORD_DEFAULT) . PHP_EOL;
