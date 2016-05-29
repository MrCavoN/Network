<?php
namespace Network;

use Exception\InvalidInputException;

require_once('Layer.php');
require_once('Neuron.php');
require_once('Exception/InvalidInputException.php');



class Network
{
    /**
     * @var integer
     */
    protected $numLayers;

    /**
     * @var array
     */
    protected $layers = array();

    /**
     * Network constructor.
     * @param array $topology
     *
     * TODO make this a vector
     */
    public function __construct(array $topology)
    {
        $this->numLayers = count($topology);
        for ($i = 0; $i < $this->numLayers; $i++) {
            $this->layers[] = new Layer();

            if ($i === $this->numLayers - 1) {
                $numberOfNeuronsNextLayer = 0;
            } else {
                $numberOfNeuronsNextLayer = $topology[$i+1];
            }

            for ($j = 0; $j <= $topology[$i]; $j++) {
                end($this->layers)->addNeuron(new Neuron($numberOfNeuronsNextLayer));
            }
        }
    }

    public function feedForward($inputValues)
    {
        rewind($this->layers);
        if (count($this->inputvals) !== current($this->layers)->getTotalNeurons() - 1) {
            throw new InvalidInputException('Amount of input values do not match input neurons');
        }

        // assigning items to the first layer
        for ($i = 0; $i < current($this->layers)->getTotalNeurons() - 1; $i++) {
            current($this->layers)->getNeurons()[$i]->setOutPutValue($inputValues($i));
        }

        // forward propegation
        while (false !== empty(current($this->layers))) {
            for ($i = 0; $i < current($this->layers)->getTotalNeurons() - 1; $i++) {
            current($this->layers)->getNeurons()[$i]
            next($this->layers);
        }
    }

    public function backProp()
    {

    }

    public function getResult()
    {

    }


    /**
     * @return int
     */
    public function getNumLayers()
    {
        return $this->numLayers;
    }

    /**
     * @param int $numLayers
     * @return Network
     */
    public function setNumLayers($numLayers)
    {
        $this->numLayers = $numLayers;
        return $this;
    }

    /**
     * @return array
     */
    public function getLayers()
    {
        return $this->layers;
    }
}

$network = new Network([3, 2, 1]);
echo '<pre>';
var_dump($network->getLayers());