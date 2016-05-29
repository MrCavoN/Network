<?php
namespace Network;

class Neuron extends Layer
{

    /**
     * @var array
     */
    protected $outputWeights = array();

    public function __construct($numberOfNeuronsNextLayer)
    {
        for ($i = 0; $i < $numberOfNeuronsNextLayer; $i++) {
            $this->outputWeights[] = mt_rand(0, 10) / 10;
        }
    }

    /**
     * @return array
     */
    public function getOutputWeights()
    {
        return $this->outputWeights;
    }

    /**
     * @param array $outputWeights
     * @return Neuron
     */
    public function setOutputWeights($outputWeights)
    {
        $this->outputWeights = $outputWeights;
        return $this;
    }

}