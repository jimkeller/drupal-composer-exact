# drupal-composer-exact
A simple script to update composer.json for a Drupal project to have exact version numbers rather than approximate numbers.

## Usage

1. Simply run: ```php drupal-composer-exact.php``` from the root of your project, where composer.json lives.
2. You should then have a file called composer.json.exact in your project root
3. Backup your existing composer.json: ```cp composer.json composer.json.back```
4. Rename composer.json.exact to composer.json: ```mv composer.json.exact composer.json```
5. Backup your composer.lock file: ```mv composer.lock composer.lock.bak```
6. Run: ```composer update nothing``` to get a new composer.lock with the updated dependencies

### Notes
This script will only update the version information projects that start with "drupal/"

It is not bulletproof. For example, some of your Drupal projects may require dependencies that are established with approximate version numbers (for example, entity_browser requires entity). I recommend you identify these dependencies and insert them into your composer.json with exact version numbers, e.g. "drupal/entity": "1.0"

Again, it is NOT bulletproof. Pay attention to what's happening in the output and double-check your composer.json before pushing/committing.


