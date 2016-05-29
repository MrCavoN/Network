<?php
namespace Network;

class Layer
{
    /**
     * @var array
     */
    protected $neurons = array();

    public function __construct()
    {

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

}