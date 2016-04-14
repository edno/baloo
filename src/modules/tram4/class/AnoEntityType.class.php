<?php

class AnoEntityType extends DataEntity
{

    function __construct AnoEntityType() 
    {
        parent::__construct(null, 'Ano');
    }

    public function getAnoTitle($params) 
    {
        $result = parent::getEntityByPropertyValue(array('num_qc' => $params['anoId']));
        return $result->nom;
    }
    
    public function getAnoSummary($params) 
    {
        return parent::getEntityByPropertyValue(array('palier' => $params['palier'], 'num_qc' => $params['anoId']));
    }
}
