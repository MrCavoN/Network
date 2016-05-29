<?php
namespace Network;

class Neuron extends Layer
{

    /**
     * @var array
     */
    protected $outputWeights = array();

    /**
     * @var integer
     */
    protected $value = 1;

    public function __construct($numberOfNeuronsNextLayer)
    {
        for ($i = 0; $i < $numberOfNeuronsNextLayer; $i++) {
            $this->outputWeights[] = mt_rand(0, 10) / 10;
        }
    }

    /**
     * @param Layer $previousLayer
     * @param $positionInLayer
     * @return int
     */
    public function feedForward(Layer $previousLayer, $positionInLayer)
    {
        $sum = 0;
        for ($i = 0; $i < count($previousLayer->getNeurons()); $i++) {
            /* @var $currentNeuron Neuron*/
            $currentNeuron  = $previousLayer->getNeurons()[$i];
            $sum           += $currentNeuron->getValue() * $this->getOutputWeight($positionInLayer);
        }
        $this->value = $sum;
    }

    /**
     * @return array
     */
    public function getOutputWeights()
    {
        return $this->outputWeights;
    }

    /**
     * @param integer $i
     * @return double
     */
    protected function getOutputWeight($i)
    {
        return $this->outputWeights[$i];
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

    /**
     * @return int
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param int $value
     * @return Neuron
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }
}