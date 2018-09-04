<?php

$composer_json_file_name = 'composer.json';
$composer_json_file_path = realpath( dirname(__FILE__) . DIRECTORY_SEPARATOR . $composer_json_file_name );

if ( !is_readable($composer_json_file_path) ) {
  exit( "Could not read composer json file: {$composer_json_file_path}" );
}

$module_search_dir = realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'modules');
$composer_json = file_get_contents($composer_json_file_path);
$composer_data = json_decode($composer_json);


if ( empty($composer_data->require) ) {
  exit( "Did not find any required packages in {$composer_json_file_path}" );
}

$required_packages = $composer_data->require;

foreach( $required_packages as $key => $version ) {

  if ( preg_match('/^drupal/', $key) ) {

    $drupal_module_name            = preg_replace('/^drupal\//', '', $key);
    $drupal_module_info_file_name  = "{$drupal_module_name}.info.yml";

    if ( !preg_match('/[^0-9\-\.]/', $version) ) {
      continue;
    }

    echo "Drupal module {$key} is not set to an exact version\n";

    preg_match('/[0-9A-Za-z\-\.@]+/', $version, $version_exact_matches);

    $version_exact = $version_exact_matches[0];

    $module_dir = new RecursiveDirectoryIterator($module_search_dir);
    $iterator   = new RecursiveIteratorIterator($module_dir);

    while ( $iterator->valid() ) {
      if ( $iterator->getFilename() == $drupal_module_info_file_name ) {

        $module_info_contents = file_get_contents($iterator->getPath() . DIRECTORY_SEPARATOR . $iterator->getFilename() );
        $module_info_contents = preg_replace('/#(.*)/m', '', $module_info_contents); //Replace comments in info file

        if ( !preg_match('/version:[\s\t]*(.*)/', $module_info_contents, $installed_version_matches) ) {
          echo "Couldn't determine installed module version for {$drupal_module_name}\n";
        }
        else {

          $installed_version_full = trim($installed_version_matches[1]);
          $installed_version_full = trim($installed_version_full, "'");
          $installed_version_full = trim($installed_version_full, '"');
          $installed_version_compare = preg_replace('/^\d+\.([0-9]+|x)\-/', '', $installed_version_full);

          $composer_version_compare = str_replace('@', '-', $version_exact);

          /*
          if ( !preg_match('/\-dev/', $version_exact) ) {
            //Replace dashes with "@" except in the case of "-dev"
            $installed_version_compare = str_replace('-', '@', $installed_version_compare); 
          }
          */

          echo "Installed version of {$drupal_module_name} is {$installed_version_compare}\n";

          if ( $installed_version_compare != $composer_version_compare ) {
            echo "Installed version of {$drupal_module_name} is different from composer version: {$installed_version_compare} vs {$composer_version_compare}\n";
            $version_exact = $installed_version_compare;
          }
        }
      }

      $iterator->next();
    }


    if ( $version != $version_exact ) {
      echo "Changing composer version of {$key} from {$version} to {$version_exact}\n";
      $composer_data->require->{$key} = $version_exact;
    }

    echo "\n";

  }

}

file_put_contents(
  $composer_json_file_path . '.exact', 
  json_encode(
    $composer_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
  )
);
