<?php
/**
 * =============================================================================
 * Copyright (c) 2010, Philip Graham
 * All rights reserved.
 *
 * This file is part of Conductor and is licensed by the Copyright holder under
 * the 3-clause BSD License.  The full text of the license can be found in the
 * LICENSE.txt file included in the root directory of this distribution or at
 * the link below.
 * =============================================================================
 *
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
namespace zpt\cdt;

use \clarinet\Criteria;

/**
 * This class provides content and localization.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class ContentProvider {

  private $_pdo;

  private $_insert = 'INSERT INTO content (key, txt) VALUES (:key, :txt)';
  private $_insertLn = 'INSERT INTO %s (id, txt) VALUES (:id, :txt)';
  private $_update = 'UPDATE content SET txt = :txt WHERE id = :id';
  private $_updateLn = 'UPDATE %s SET txt = :txt WHERE id = :id';

  public function getContent($key, $language = null, $defaultText = null) {
    if ($language === 'en') {
      $language = null;
    }

    $c = new Criteria();

    $table = 'content';
    $c->setTable($table);
    $c->addSelect("$table.txt en");
    if ($language !== null) {
      $table .= "_$language";
      $c->addLeftJoin($table, 'id');
      $c->addSelect("$table.txt $language");
    }
    $c->addEquals('key', $key);

    $stmt = $this->_pdo->prepare($c);
    $stmt->execute($c->getParameters());

    $result = $stmt->fetch();
    if ($result === null) {
      if ($defaultText === null) {
        throw new Exception("Unable to retrieve text for '$key'");
      }

      if ($language === null) {
        $stmt = $this->_pdo->prepare($this->_insert);
        $stmt->execute(array('key' => $key, 'txt' => $defaultText));

        return $defaultText;
      } else {
        return "{en:$defaultText}";
      }
    } else if ($language !== null) {
      if ($result[$language] !== null) {
        return $result[$language];
      } else {
        return "{en:$defaultText}";
      }
    } else {
      return $result['en'];
    }
  }

  /**
   * @RequestType post
   */
  public function setContent($key, $text, $language = null) {
    if ($language === 'en') {
      $language = null;
    }

    $c = new Criteria();
    $c->setTable('content');
    $c->addEquals('key', $key);
    $stmt = $this->_pdo->prepare($c);
    $stmt->execute($c->getParameters());

    $result = $stmt->fetch();
    if ($result === null) {
      $stmt = $this->_pdo->prepare($this->_insert);
      if ($language === null) {
        $stmt->execute($key, $text);
      } else {
        $stmt->execute($key, '');

        $ctntId = $this->_pdo->lastInsertId();
        $stmtLn = $this->_pdo->prepare(sprintf($this->_insertLn,
          Criteria::escapeFieldName("content_$language")));
        $stmtLn->execute(array('id' => $ctntId, 'txt' => $text));
      }
    } else {
      $ctntId = (int) $result['id'];
      if ($language === null) {
        $stmt = $this->_pdo->prepare($this->_update);
        $stmt->execute(array('id' => $ctntId, 'txt' => $text));

      } else {
        $c = new Criteria();
        $c->setTable("content_$language");
        $c->addEquals('id', $ctntId);
        $stmt = $this->_pdo->prepare($c);
        $stmt->execute($c->getParameters());

        $result = $stmt->fetch();
        if ($result === null) {
          $stmtLn = $this->_pdo->prepare(sprintf($this->_insertLn,
            Criteria::escapeFieldName("content_$language")));
          $stmtLn->execute(array('id' => $ctntId, 'txt' => $text));
        } else {
          $stmtLn = $this->_pdo->prepare(sprintf($this->_updateLn,
            Criteria::escapeFieldName("content_$language")));
          $stmtLn = $this->_pdo->execute(array('id' => $ctntId, 'txt' => $text));
        }
      }
    }
  }

  public function setPdo($pdo) {
    $this->_pdo = $pdo;
  }

}
