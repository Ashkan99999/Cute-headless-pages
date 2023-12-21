<?php

namespace Drupal\cute_taxonomy\taxonomy;

/**
 * Interface TaxonomyServiceInterface.
 */
interface TaxonomyServiceInterface {

  /**
   * @param $vid
   *
   * @return mixed
   */
  public function getVocabulary($vid);

  /**
   * @param $tid
   *
   * @return mixed
   */
  public function getTerm($tid);

  /**
   * @param $vid
   *
   * @return mixed
   */
  public function getTerms($vid);

  /**
   * @param $data
   *
   * @return mixed
   */
  public function createTerm($data);

  /**
   * @param $tid
   * @param $data
   *
   * @return mixed
   */
  public function updateTerm($tid, $data);

  /**
   * @param $tid
   *
   * @return mixed
   */
  public function deleteTerm($tid);

  /**
   * @param $vid
   * @param $parent
   *
   * @return mixed
   */
  public function getChildrenByVid($vid, $parent, $depth);

  /**
   * @param $vid
   *
   * @return mixed
   */
  public function getParenstByVid($vid);

  /**
   * @param $tid
   *
   * @return mixed
   */
  public function getParent($tid);

  /**
   * @param $tid
   *
   * @return mixed
   */
  public function getChildren($tid);

  public function filterTaxonomy($data);

}
