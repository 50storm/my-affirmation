<?php

namespace MyAffirmationModel;

/**
 * Affirmation class
 */
class Affirmation
{
    /**
     * Table Name
     */
    public const AFFIRMATION_TABLE_NAME = 'affirmations';

    /**
     * insert_affirmation function
     *
     * @param string $affirmation
     * @return (int|false)
     */
    public static function insert_affirmation($affirmation = "")
    {
        global $wpdb;
        $table_name = $wpdb->prefix . Affirmation::AFFIRMATION_TABLE_NAME;
        $wpdb->insert(
            $table_name,
            array(
        'affirmation' => $affirmation
      ),
            array(
        '%s'
      )
        );
        return $wpdb->insert_id;
    }

    /**
     * select_one_affirmation_randomly function
     *
     * @return array
     */
    public static function select_one_affirmation_randomly()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . Affirmation::AFFIRMATION_TABLE_NAME;
        $results = $wpdb->get_results("SELECT * FROM $table_name ORDER BY RAND() LIMIT 1", ARRAY_A);
        return $results;
    }

    /**
     * select_one_affirmation_by_id function
     *
     * @param integer $id
     * @return array[0]
     */
    public static function select_one_affirmation_by_id($id=0)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . Affirmation::AFFIRMATION_TABLE_NAME;
        $results = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id),
            ARRAY_A
        );
        return $results[0];
    }

    /**
     * select_all function
     *
     * @return array
     */
    public static function select_all()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . Affirmation::AFFIRMATION_TABLE_NAME;
        $results = $wpdb->get_results("SELECT * FROM $table_name ORDER BY id", ARRAY_A);
        return $results;
    }

    /**
     * update function
     *
     * @param array $parms
     * @return (int|false)
     */
    public static function update($parms = [])
    {
        global $wpdb;
        $table_name = $wpdb->prefix . Affirmation::AFFIRMATION_TABLE_NAME;
        return $wpdb->update(
            $table_name,
            array(
          'affirmation' => $parms['affirmation']
        ),
            array( 'id' => $parms['id'] ),
            array(
          '%s',
          '%d',
        ),
        );
    }

    /**
     * delete function
     *
     * @param [type] $id
     * @return  (int|false)
     */
    public static function delete($id=null)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . Affirmation::AFFIRMATION_TABLE_NAME;
        return $wpdb->delete($table_name, array( 'id' => $id ), array( '%d' ));
    }
}
