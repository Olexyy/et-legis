# ET-LEGIS PROJECT

## Article system and portal

This project uses:

 - `composer template for Drupal 8` projects as base template
 - admin theme `Seven` for rendering
 - `install_profile` for install

Correct way to install as expected is:

```
drush si install_profile --site-name='Et-legis' --account-name=admin --account-pass=Aa111! --db-url=mysql://root:root@localhost:3306/et-legis -y && drush csim -y && drush locale:update && drush csim -y && drush cr
drush si install_profile --site-name='Et-legis' --account-name=admin --account-pass=Aa111! && drush locale:update && drush csim -y && drush cr
```