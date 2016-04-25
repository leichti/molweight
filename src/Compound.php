<?php

namespace Leichti\Molweight;

class Compound {

    protected $compoundsArray = array();
    protected $compoundString = "";
    protected $elements = array();

    public function __construct(String $compoundFormula)
    {
        $this->initCompound($compoundFormula);
    }

    public function initCompound($compoundFormula)
    {
        $this->compoundString = $compoundFormula;
        $this->compoundsArray = array();
        $this->elements = array();
    }

    public function getActualString()
    {
        return $this->compoundString;
    }

    protected function filterHSCSpecificBrackets()
    {
        // https://regex101.com/r/fB2hW6/1
        $this->compoundString = preg_replace("/(?!^)(\([^\(]*\)$)/", "", $this->compoundString);
    }


    /**
     * Cut out compounds from brackets !!Reads the compoundString property!! e.x: Fe(OH)2 will create an entry in the compoundArray and the Fe will remain in the compoundString property.
     * @return $this
     */
    protected function cutOutBracketsAndSaveToArray()
    {
        
        $pattern = "/(?=^)([0-9]*\([A-Za-z0-9]*\)[0-9\.]*)|(\([A-Za-z0-9]*\)[0-9\.]*)/";
        preg_match_all($pattern, $this->compoundString, $matches);
        $this->compoundString = preg_replace($pattern, "", $this->compoundString);

        // not sure for what we need $matches[1]
        foreach($matches[0] as $match)
        {
            $pattern = "/\)[0-9\.]+/";
            preg_match($pattern, $match, $amountArray);

            $pattern = "/[\(\)]+[0-9\.]*/";
            $match = preg_replace($pattern, "", $match);

            $amount = !empty($amountArray) ? substr($amountArray[0], 1) : 1;
            $this->addToCompoundsArray($match, $amount);
        }

        return $this;
    }

    /* Split compounds like 2CaO*2Al2O3 into subcompounds */
    protected function cutOutCompoundsAndSaveToArray($input)
    {
        $compounds = explode("*", $input);

        foreach($compounds as $compound) {
            preg_match("/^[0-9\.]+/", $compound, $amountArray);
            $compound = preg_replace("/^[0-9\.]+/", "", $compound);
            $amount = (!empty($amountArray)) ? $amountArray[0] : 1 ;
            $this->addToCompoundsArray($compound, $amount);
        }
    }

    public function cleanUpCompoundString(){
        $this->compoundString = preg_replace("/^[^a-zA-Z\(\)0-9\.]*(.*?)[^a-zA-Z0-9\)]*$/", "$1", $this->compoundString);
    }

    public function getElementsFromLoadedCompound($compound = false)
    {

        if($compound)
            $this->initCompound($compound);

        $this->cleanUpCompoundString();
        $this->filterHSCSpecificBrackets();
        $this->cutOutBracketsAndSaveToArray();

        $this->cleanUpCompoundString();
        $this->cutOutCompoundsAndSaveToArray($this->compoundString);

        foreach($this->compoundsArray as $compound) {
            $this->splitCompoundToElements($compound["name"], $compound["amount"]);
        }

        return $this->elements;
    }

    public function splitCompoundToElements($compound, $compoundAmount = 1)
    {
        preg_match_all("([A-Z]{1}[a-z]*[0-9\.]*)", $compound, $elementsWithAmount);

        $elements = preg_replace("/([0-9\.]+)/", "", $elementsWithAmount[0]);

        foreach($elementsWithAmount[0] as $key=>$elementWithNumber) {
            preg_match("/([0-9\.]+)/", $elementWithNumber, $amount);
            $amount = (isset($amount[0])) ? $amount[0] : 1;
            $this->addElement($elements[$key], $amount*$compoundAmount);
        }
    }

    public function addElement($element, $amount)
    {
        if(isset($this->elements[$element])) {
            $this->elements[$element] += $amount;
        }
        else {
            $this->elements[$element] = $amount;
        }
    }

    /**
     * Has to be filterHSCSpecificBrackets() and cutOutBracketsAndSaveToArray().
     * Still allowed to be a compound
     *
     * @param $compound
     * @param $amountArray
     * @return mixed
     */
    public function addToCompoundsArray($compound, $amount)
    {
        if($this->isAComplexCompound($compound)) {
            $this->cutOutCompoundsAndSaveToArray($compound);
        }

        $this->compoundsArray[] = array(
            "name" => $compound,
            "amount" => $amount
        );
    }

    public function isAComplexCompound($compound)
    {
        return (strpos($compound, "*")===false) ? false : true;
    }
}
