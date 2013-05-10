<?php
namespace zpt\cdt\crud;

use \zpt\rest\Request;
use \StdClass;

/**
 * This class provides common SPF parameter parsing.
 *
 * TODO Document parsed format(s)
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class SpfParser {

    public function parseRequest(Request $request)
    {
      $query = $request->getQuery();
      return isset($query['spf'])
        ? json_decode($query['spf'])
        : new StdClass();
    }

    public function populateQueryBuilder(StdClass $spf, $qb)
    {
        $this->populateQueryBuilderSorting($spf, $qb);
        $this->populateQueryBuilderPaging($spf, $qb);
        $this->populateQueryBuilderFilters($spf, $qb);
    }

    public function populateQueryBuilderSorting(StdClass $spf, $qb)
    {
        if (isset($spf->sort)) {
            foreach ($spf->sort AS $sort) {
                $dir = isset($sort->dir) ? $sort->dir : 'asc';
                $qb->addSort($sort->field, $dir);
            }
        }
    }

    public function populateQueryBuilderPaging(StdClass $spf, $qb)
    {
        if (isset($spf->page)) {
            $limit = $spf->page->limit;
            $offset = isset($spf->page->offset)
                ? $spf->page->offset
                : null;
            $qb->setLimit($limit, $offset);
        }
    }

    public function populateQueryBuilderFilters(StdClass $spf, $qb)
    {
        if (isset($spf->filter)) {
            foreach ($spf->filter AS $column => $value) {
                $qb->addFilter($column, $value);
            }
        }
    }
}
