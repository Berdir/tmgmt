<?php


/**
 * Interface for source ui controllers.
 *
 * @ingroup tmgmt_source
 */
interface TMGMTSourceUIControllerInterface extends TMGMTPluginBaseInterface {

  /**
   * Form callback for the job item review form.
   */
  public function reviewForm($form, &$form_state, TMGMTJobItem $item);

  /**
   * Validation callback for the job item review form.
   */
  public function reviewFormValidate($form, &$form_state, TMGMTJobItem $item);

  /**
   * Submit callback for the job item review form.
   */
  public function reviewFormSubmit($form, &$form_state, TMGMTJobItem $item);

  /**
   * Implements hook_menu().
   *
   * @see tmgmt_ui_menu().
   */
  public function hook_menu();

  /**
   * Implements hook_forms().
   *
   * @see tmgmt_ui_forms().
   */
  public function hook_forms();

  /**
   * Implements hook_views_default_views().
   *
   * @see tmgmt_ui_views_default_views().
   */
  public function hook_views_default_views();

}
