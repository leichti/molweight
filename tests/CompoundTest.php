<?php

class CompoundTest extends PHPUnit_Framework_TestCase
{

    /** @test */
    function it_returns_the_correct_array_for_compounds()
    {
        $compound = new Leichti\Molweight\Compound("Al2O3");
        $this->assertEquals($compound->getElementsFromLoadedCompound(), ["Al"=>2, "O"=>3]);

        $compound = new Leichti\Molweight\Compound("2Al2O3*2SiO2*(OH)3");
        $elementsArray = $compound->getElementsFromLoadedCompound();
        $this->assertEquals($elementsArray, ["Al"=>4, "O"=>13, "Si" => 2, "H" => 3]);

        $compound = new Leichti\Molweight\Compound("(SiO2)6(aq+)");
        $elementsArray = $compound->getElementsFromLoadedCompound();
        $this->assertEquals($elementsArray, ["Si"=>6, "O"=>12]);

        $compound = new Leichti\Molweight\Compound("3H3O");
        $elementsArray = $compound->getElementsFromLoadedCompound();
        $this->assertEquals(["H"=>9, "O"=>3], $elementsArray);

        $compound = new Leichti\Molweight\Compound("(H3O)3Fe3*2Al2O3*(SO4)2*(OH)6(AQ)");
        $elementsArray = $compound->getElementsFromLoadedCompound();
        $this->assertEquals(["H"=>15, "O"=>23, "Fe" => 3, "Al" => 4, "S" => 2], $elementsArray);

        $compound = new Leichti\Molweight\Compound("-&*(SiO2.1)6(aq+)*&");
        $compound->cleanUpCompoundString();
        $this->assertEquals("(SiO2.1)6(aq+)", $compound->getActualString());
    }

}