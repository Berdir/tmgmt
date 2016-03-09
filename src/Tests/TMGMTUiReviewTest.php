<?php

/**
 * @file
 * Contains Drupal\tmgmt\Tests\TMGMTUiReviewTest.
 */

namespace Drupal\tmgmt\Tests;

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\file\Entity\File;
use Drupal\node\Entity\Node;

/**
 * Verifies the UI of the review form.
 *
 * @group tmgmt
 */
class TMGMTUiReviewTest extends EntityTestBase {

  public static $modules = ['ckeditor', 'tmgmt_content', 'image', 'node'];

  /**
   * {@inheritdoc}
   */
  function setUp() {
    parent::setUp();

    $this->drupalCreateContentType(array('type' => 'test_bundle'));

    $this->loginAsAdmin(array(
      'create translation jobs',
      'submit translation jobs',
      'create test_bundle content',
    ));

    file_unmanaged_copy(DRUPAL_ROOT . '/core/misc/druplicon.png', 'public://example.jpg');
    $this->image = File::create(array(
      'uri' => 'public://example.jpg',
    ));
    $this->image->save();
  }

  /**
   * Tests of the job item review process.
   */
  public function testReviewForm() {
    // Create the field body with multiple delta.
    $field_storage = FieldStorageConfig::create(array(
      'field_name' => 'body_test',
      'entity_type' => 'node',
      'type' => 'text',
      'cardinality' => -1,
      'translatable' => TRUE,
    ));
    $field_storage->save();
    FieldConfig::create(array(
      'field_storage' => $field_storage,
      'bundle' => 'test_bundle',
    ))->save();

    // Create the field image with multiple value and delta.
    $field_storage = FieldStorageConfig::create(array(
      'field_name' => 'image_test_multi',
      'entity_type' => 'node',
      'type' => 'image',
      'cardinality' => -1,
      'translatable' => TRUE,
    ));
    $field_storage->save();
    FieldConfig::create(array(
      'field_storage' => $field_storage,
      'bundle' => 'test_bundle',
    ))->save();

    // Create the field image with multiple value and delta.
    $field_storage = FieldStorageConfig::create(array(
      'field_name' => 'image_test_single',
      'entity_type' => 'node',
      'type' => 'image',
      'cardinality' => 1,
      'translatable' => TRUE,
    ));
    $field_storage->save();
    FieldConfig::create(array(
      'field_storage' => $field_storage,
      'bundle' => 'test_bundle',
    ))->save();

    // Create two images.
    $image1 = array(
      'target_id' => $this->image->id(),
      'alt' => $this->randomMachineName(),
      'title' => $this->randomMachineName(),
    );
    $image2 = array(
      'target_id' => $this->image->id(),
      'alt' => $this->randomMachineName(),
      'title' => $this->randomMachineName(),
    );

    // Create the node.
    $settings = array(
      'title' => $this->randomMachineName(),
      'type' => 'test_bundle',
      'body_test' => array(
        $this->randomMachineName(),
        $this->randomMachineName(),
      ),
      'image_test_single' => $image1,
      'image_test_multi' => array($image1, $image2),
    );
    $node = Node::create($settings);
    $node->save();

    // Create a Job with the node.
    $job = tmgmt_job_create('en', 'de');
    $job->translator = 'test_translator';
    $job->save();
    $job_item = tmgmt_job_item_create('content', 'node', $node->id(), array('tjid' => $job->id()));
    $job_item->save();

    // Access to the review form.
    $this->drupalGet('admin/tmgmt/items/1');

    // Test that all the items are being displayed.
    $this->assertRaw('name="title|0|value[source]"');
    $this->assertRaw('name="body_test|0|value[source]"');
    $this->assertRaw('name="body_test|1|value[source]"');
    $this->assertRaw('name="image_test_multi|0|title[source]"');
    $this->assertRaw('name="image_test_multi|0|alt[source]"');
    $this->assertRaw('name="image_test_multi|1|title[source]"');
    $this->assertRaw('name="image_test_multi|1|alt[source]"');
    $this->assertRaw('name="image_test_single|0|title[source]"');
    $this->assertRaw('name="image_test_single|0|alt[source]"');

    // Check the labels for the title.
    $this->assertEqual($this->xpath('//*[@id="tmgmt-ui-element-title-wrapper"]/table/tbody/tr[1]/th'), NULL);
    $this->assertEqual($this->xpath('//*[@id="tmgmt-ui-element-title-wrapper"]/table/tbody/tr[2]/td[1]/div[1]/label'), NULL);

    // Check the labels for the multi delta body.
    $delta = $this->xpath('//*[@id="tmgmt-ui-element-body-test-wrapper"]/table/tbody/tr[1]/td[1]/div[1]/label');
    $this->assertEqual($delta[0], 'Delta #0');
    $delta = $this->xpath('//*[@id="tmgmt-ui-element-body-test-wrapper"]/table/tbody/tr[3]/td[1]/div[1]/label');
    $this->assertEqual($delta[0], 'Delta #1');

    // Check the labels for the multi delta/multi value image.
    $delta = $this->xpath('//*[@id="tmgmt-ui-element-image-test-multi-wrapper"]/table/tbody[1]/tr[1]/th');
    $this->assertEqual($delta[0], 'Delta #0');
    $label = $this->xpath('//*[@id="tmgmt-ui-element-image-test-multi-wrapper"]/table/tbody[1]/tr[2]/td[1]/div[1]/label');
    $this->assertEqual($label[0], 'Alternative text');
    $label = $this->xpath('//*[@id="tmgmt-ui-element-image-test-multi-wrapper"]/table/tbody[1]/tr[4]/td[1]/div[1]/label');
    $this->assertEqual($label[0], 'Title');
    $delta = $this->xpath('//*[@id="tmgmt-ui-element-image-test-multi-wrapper"]/table/tbody[2]/tr[1]/th');
    $this->assertEqual($delta[0], 'Delta #1');
    $label = $this->xpath('//*[@id="tmgmt-ui-element-image-test-multi-wrapper"]/table/tbody[2]/tr[2]/td[1]/div[1]/label');
    $this->assertEqual($label[0], 'Alternative text');
    $label = $this->xpath('//*[@id="tmgmt-ui-element-image-test-multi-wrapper"]/table/tbody[2]/tr[4]/td[1]/div[1]/label');
    $this->assertEqual($label[0], 'Title');

    // Check the labels for the multi value image.
    $this->assertEqual($this->xpath('//*[@id="tmgmt-ui-element-image-test-single-wrapper"]/table/tbody/tr[1]/th'), NULL);
    $label = $this->xpath('//*[@id="tmgmt-ui-element-image-test-single-wrapper"]/table/tbody/tr[1]/td[1]/div[1]/label');
    $this->assertEqual($label[0], 'Alternative text');
    $label = $this->xpath('//*[@id="tmgmt-ui-element-image-test-single-wrapper"]/table/tbody/tr[3]/td[1]/div[1]/label');
    $this->assertEqual($label[0], 'Title');
  }
}
