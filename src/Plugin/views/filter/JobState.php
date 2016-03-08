<?php

/**
 * @file
 * Contains \Drupal\tmgmt\Plugin\views\filter\JobState.
 */

namespace Drupal\tmgmt\Plugin\views\filter;

use Drupal\views\Plugin\views\filter\ManyToOne;

/**
 * Filter based on job state.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("tmgmt_job_state_filter")
 */
class JobState extends ManyToOne {

  /**
   * Where the $query object will reside.
   *
   * @var \Drupal\views\Plugin\views\query\Sql
   */
  public $query = NULL;

  /**
   * Gets the values of the options.
   *
   * @return array
   *   Returns options.
   */
  public function getValueOptions() {
    return $this->valueOptions = array(
      'open_jobs' => '- Open jobs -',
      '0' => 'Unprocessed',
      'in_progress' => 'In progress',
      'needs_review' => 'Needs review',
      '2' => 'Rejected',
      '4' => 'Aborted',
      '5' => 'Finished',
    );
  }

  /**
   * Set the operators.
   *
   * @return array
   *   Returns operators.
   */
  function operators() {
    $operators = array(
      'job_state' => array(
        'title' => $this->t('Job State'),
        'short' => $this->t('job state'),
        'values' => 1,
      )
    );
    return $operators;
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    $key = reset($this->value);
    $field = $this->field;
    $table = $this->table;
    switch ($key) {
      case 'needs_review':
        $table_alias = 'job_item';
        // Create a sub query to add the state of job item to the view.
        $sub_query = \Drupal::database()->select('tmgmt_job_item', $table_alias);
        $sub_query->addField($table_alias, 'tjid');
        // Add a where clause to check if there are job items with state 2.
        $sub_query->condition("$table_alias.state", 2, '=');

        // Select all job items that are in the sub query.
        $this->query->addWhere($this->options['group'], 'tjid', $sub_query, 'IN');
        $this->query->addWhere($this->options['group'], "$table.$field", '1', '=');
        break;
      case 'in_progress':
        $table_alias = 'job_item';
        // Create a sub query to add the state of job item to the view.
        $sub_query = \Drupal::database()->select('tmgmt_job_item', $table_alias);
        $sub_query->addField($table_alias, 'tjid');
        // Add a where clause to check if there are job items with state 2.
        $sub_query->condition("$table_alias.state", 2, '=');

        // Select all job items that are not in the sub query.
        $this->query->addWhere($this->options['group'], 'tjid', $sub_query, 'NOT IN');
        $this->query->addWhere($this->options['group'], "$table.$field", '1', '=');
        break;
      case 'open_jobs':
        $this->query->addWhere($this->options['group'], "$table.$field", array(0, 1, 2, 3), 'IN');
        break;
      default:
        $this->query->addWhere($this->options['group'], "$table.$field", $key, '=');
        break;
    }
  }
}
