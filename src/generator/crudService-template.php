<?php
namespace ${ns};

use \clarinet\Criteria;
use \clarinet\Persister;
use \clarinet\Transformer;

${if:gatekeeper ISSET}
  use \${gatekeeper} as Gatekeeper;
${else}
  use \conductor\crud\DefaultGatekeeper as Gatekeeper;
${fi}
use \conductor\Conductor;

/**
 * This is CRUD service class for a ${model} class.
 *
 * This class is generated.  Do NOT modify this file.  Instead, modify the
 * model class used for generation then regenerate this class.
 *
 * @Service( name = ${proxyName} )
 * @CsrfToken conductorsessid
 * @Requires ${autoloader}
 */
class ${className} { 
  private $_gatekeeper;

  public function __construct() {
    // Ensure that conductor is initialized
    Conductor::init();

    $this->_gatekeeper = new Gatekeeper('${model}');
  }

  /**
   * @RequestType post
   */
  public function create(array $params) {

    try {
      $transformer = Transformer::get('${model}');
      $model = $transformer->fromArray($params);

      $this->_gatekeeper->checkCanCreate($model);

      $persister = Persister::get($model);
      $persister->create($model);
    } catch (\clarinet\Exception $e) {
      throw new \Exception($this->_parseExceptionMessage($e->getMessage()));
    }

  }

  /**
   * Retrieve instances that match the given sort-paging-filtering criteria.
   *
   * @param array $spf
   */
  public function retrieve($spf) {
    try {

      $persister = Persister::get('${model}');
      $transformer = Transformer::get('${model}');

      $c = new Criteria();

      // Apply paging, sorting and filters to the criteria
      if (isset($spf->filter)) {
        foreach ($spf->filter AS $column => $value) {
          $c->addEquals($column, $value);
        }
      }

      if (isset($spf->sort)) {
        foreach ($spf->sort AS $column => $direction) {
          if ($direction === Criteria::SORT_DESC) {
            $c->addSort($column, Criteria::SORT_DESC);
          } else {
            $c->addSort($column);
          }
        }
      }

      if (isset($spf->page)) {
        $limit = $spf->page->limit;
        $offset = isset($spf->page->offset)
          ? $spf->page->offset
          : null;

        $c->setLimit($limit, $offset);
      }

      // Retrieve the models that match the given spf
      $models = $persister->retrieve($c);
      $total = $persister->count($c);

      $data = array();
      foreach ($models AS $model) {
        if ($this->_gatekeeper->canRead($model)) {
          $data[] = $transformer->asArray($model);
        }
      }

      return array(
        'data' => $data,
        'total' => $total
      );
    } catch (\clarinet\Exception $e) {
      throw new \Exception($this->_parseExceptionMessage($e->getMessage()));
    }
  }

  /**
   * @RequestType post
   */
  public function update(array $params) {
    try {
      $transformer = Transformer::get('${model}');
      $model = $transformer->fromArray($params);

      $persister = Persister::get('${model}');
      $original = $persister->getById($model->get${idColumn}());
      
      $this->_gatekeeper->checkCanWrite($original);

      $persister->update($model);
    } catch (\clarinet\Exception $e) {
      throw new \Exception($this->_parseExceptionMessage($e->getMessage()));
    }
  }

  /**
   * @RequestType post
   */
  public function delete(array $ids) {
    try {
      $persister = Persister::get('${model}');

      $c = new Criteria();
      $c->addIn('${idColumn}', $ids);
      $models = $persister->retrieve($c);

      foreach ($models AS $model) {
        $this->_gatekeeper->checkCanDelete($model);
      }

      foreach ($models AS $model) {
        $persister->delete($model);
      }
    } catch (\clarinet\Exception $e) {
      throw new \Exception($this->_parseExceptionMessage($e->getMessage()));
    }
  }

  private function _parseExceptionMessage($msg) {
    // TODO Move this into it's own class -- maybe in clarinet
    $sqlstateRe = '/SQLSTATE\[(\d+)\]:\s*(.*)$/';
    $errMsgRe = '/.+:\s*\d+\s*(.+)$/';
    $dupEntryRe = '/1062 Duplicate entry \'(.+)\' for key \'(.+)\'/';

    if (preg_match($sqlstateRe, $msg, $matches)) {
      switch ((int) $matches[1]) {
        case 23000:
        if (preg_match($dupEntryRe, $matches[2], $msgMatches)) {
          return "A ${display} with {$msgMatches[2]} '{$msgMatches[1]}' already exists";
        }
        break;

      }

      if (preg_match($errMsgRe, $matches[2], $msgMatches)) {
        return $msgMatches[1];
      }
      return $matches[2];
    }
    return $msg;
  }
}
