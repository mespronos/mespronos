#!/bin/bash
# This file lives in the travis-ci subdirectory

set -e $DRUPAL_TI_DEBUG

# Ensure the right Drupal version is installed.
# Note: This function is re-entrant.
drupal_ti_ensure_drupal

# Ensure the module is linked into the codebase.
drupal_ti_ensure_module_linked

# Require that minimal profile enables decoupled_auth.
ls $DRUPAL_TI_DRUPAL_DIR
ls $DRUPAL_TI_DRUPAL_DIR/modules/mespronos
cp $DRUPAL_TI_DRUPAL_DIR/modules/mespronos/travis-ci/minimal.info.yml $DRUPAL_TI_DRUPAL_DIR/core/profiles/minimal

# Enable main module and submodules.
drush en -y mespronos mespronos_group