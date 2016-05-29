<?php
namespace Network;

class Layer
{
    /**
     * @var array
     */
    protected $neurons = array();

    /**
     * @var Layer
     */
    protected $previousLayer = null;

    /**
     * @var Layer
     */
    protected $nextLayer = null;

    /**
     * @var string
     */
    protected $name;

    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * @return array
     */
    public function getNeuronValuesAsArray()
    {
        $return = [];
        for ($i = 0; $i < count($this->neurons) -1; $i++) {
            $return[] = $this->neurons[$i]->getValue();
        }
        return $return;
    }

    /**
     * @param Neuron $neuron
     * @return Layer
     */
    public function addNeuron(Neuron $neuron)
    {
        $this->neurons[] = $neuron;
        return $this;
    }

    /**
     * @return array
     */
    public function getNeurons()
    {
        return $this->neurons;
    }

    /**
     * @return int
     */
    public function getTotalNeurons()
    {
        return count($this->neurons);
    }

    /**
     * @return Layer
     */
    public function getNextLayer()
    {
        return $this->nextLayer;
    }

    /**
     * @param Layer $nextLayer
     * @return Layer
     */
    public function setNextLayer($nextLayer = null)
    {
        $this->nextLayer = $nextLayer;
        return $this;
    }

    /**
     * @return Layer
     */
    public function getPreviousLayer()
    {
        return $this->previousLayer;
    }

    /**
     * @param Layer $previousLayer
     * @return Layer
     */
    public function setPreviousLayer($previousLayer)
    {
        $this->previousLayer = $previousLayer;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Layer
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }
}