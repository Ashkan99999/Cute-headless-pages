# Cute-headless-pages

This module is designed to furnish Drupal 10 with a headless structure.
which consist of several sub-modules with the following names:
cute comment
cute content
cute user
cute taxonomy
cute file
cute headless

## Cute Comment
This module is designed to provide a headless structure for comments.

## Cute Content
This module is designed to provide a headless structure for content.

## Cute User
This module is designed to provide a headless structure for users.

## Cute Taxonomy
This module is designed to provide a headless structure for taxonomy.

## Cute File
This module is designed to provide a headless structure for files.

## Cute Headless
This module is designed to provide a headless structure for Drupal 10.

## Installation
To install this module, do the following:
1. Download the module 
2. Extract the module to the modules folder of your Drupal 10 installation
3. Enable the module by running the following command in the root directory of your Drupal 10 installation:
```bash
drush en cute_headless
```
4. Enable the sub-modules by running the following command in the root directory of your Drupal 10 installation:
```bash
drush en cute_comment
drush en cute_content
drush en cute_user
drush en cute_taxonomy
drush en cute_file
```
5. Install the module by running the following command in the root directory of your Drupal 10 installation:
```bash
drush en cute_headless
```
6. Configure the module by running the following command in the root directory of your Drupal 10 installation:
```bash
drush en cute_headless
```
7. Clear the cache by running the following command in the root directory of your Drupal 10 installation:
```bash
drush cr


## Usage

## Contributing
Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

go to /admin/config/services/rest and enable the following resources:
- Comment
- Content
- File
- Taxonomy
- user
 go to /admin/people/permissions and enable the following permissions:
- Access GET on Comment resource
- Access GET on Content resource
- Access GET on File resource
- Access GET on Taxonomy resource
- Access GET on User resource
- Access POST on Comment resource
- Access POST on Content resource
- Access POST on File resource
- Access POST on Taxonomy resource
- Access POST on User resource
- Access PATCH on Comment resource
- Access PATCH on Content resource
- Access PATCH on File resource
- Access PATCH on Taxonomy resource
- Access PATCH on User resource
- Access DELETE on Comment resource
- Access DELETE on Content resource
- Access DELETE on File resource
- Access DELETE on Taxonomy resource
- Access DELETE on User resource
- Access POST on Comment resource
- Access POST on Content resource
- Access POST on File resource
...


## License
This module is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details

