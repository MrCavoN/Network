<?php
namespace Network;

use Network\Exception\InvalidInputException;

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
     * @var float
     */
    protected $rms = 0.0;

    /**
     * Network constructor.
     * @param array $topology
     *
     * TODO make this a vector
     */
    public function __construct(array $topology)
    {
        /* @var $previousLayer Layer*/
        $this->numLayers = count($topology);
        $previousLayer   = null;

        for ($i = 0; $i < $this->numLayers; $i++) {
            $currentLayer   = new Layer('Layer' . $i);
            $this->layers[] = $currentLayer;
            $currentLayer->setPreviousLayer($previousLayer);

            if ($previousLayer !== null) {
                $previousLayer->setNextLayer($currentLayer);
            }

            for ($j = 0; $j <= $topology[$i]; $j++) {
                if (false === empty($topology[$i+1])) {
                    $currentLayer->addNeuron(new Neuron($topology[$i+1])); // number of neurons in the next layer NOT including the bias neuron
                } else {
                    $currentLayer->addNeuron(new Neuron(0));
                }
            }
            $previousLayer = $currentLayer;
        }
    }

    /**
     * @param  array $inputValues
     * @return array
     * @throws InvalidInputException
     */
    public function feedForward($inputValues)
    {
        /* @var $currentLayer Layer */
        /* @var $currentNeuron Neuron */
        for ($i = 0; $i < count($this->layers); $i++) {
            $currentLayer = $this->layers[$i];
            if ($i === 0) { // setting the values of the first layer
                if (count($inputValues) !== count($currentLayer->getNeurons()) - 1) {
                    throw new InvalidInputException('Amount of input values do not match input neurons');
                }
                /* set values of first layer*/
                for ($j = 0; $j < count($inputValues); $j++) {
                    $currentNeuron = $currentLayer->getNeurons()[$j];
                    $currentNeuron->setValue($inputValues[$j]);
                }
            } else {
                /* feedforward trough layers*/
                for ($j = 0; $j < count($currentLayer->getNeurons()) - 1; $j++) { // for each neuron in the current layer except the bias neuron
                    $currentNeuron = $currentLayer->getNeurons()[$j];
                    $currentNeuron->feedForward($currentLayer->getPreviousLayer(), $j);
                }
            }

            /* stopcondition */
            if ($currentLayer->getNextLayer() === null) {
                return $currentLayer->getNeuronValuesAsArray();
            }
        }
    }

    /**
     * @param $expectedValues
     */
    public function backProp($expectedValues)
    {
        /* @var $outputLayer Layer */
        /* @var $currentLayer Layer */
        /* @var $currentNeuron Neuron */
        /* Calculating the overall net error*/
        $outputLayer = end($this->getLayers());
        $error       = 0;
        for ($i = 0; $i < $outputLayer->getTotalNeurons() -1; $i++) {
            $currentNeuron = $outputLayer->getNeurons()[$i];
            $difference    = $expectedValues[$i] - $currentNeuron->getValue();
            $error        += $difference * $difference;
        }
        $error     = $error / ($outputLayer->getTotalNeurons() -1);
        $this->rms = sqrt($error);

        // Calclulate output layer gradients
        for ($i = 0; $i < $outputLayer->getTotalNeurons() -1; $i++) {
            for ($i = 0; $i < $outputLayer->getTotalNeurons() -1; $i++) {
                $currentNeuron = $outputLayer->getNeurons()[$i];
                $currentNeuron->calculateOutputGradients($expectedValues[$i]);
            }
        }

        // Calculate hidden layer gradients
        for ($i = count($this->getLayers()) - 2; $i > 0; $i--) {
            $currentLayer = $this->layers[$i];
            for ($i = 0; $i < $currentLayer->getTotalNeurons() -1; $i++) {
                $currentNeuron = $outputLayer->getNeurons()[$i];
                $currentNeuron->calculateHiddenGradients($currentLayer->getNextLayer());
            }
        }

        // calculate the new weights
        for ($i = count($this->getLayers()) - 1; $i > 0; $i--) {
            $currentLayer = $this->layers[$i];
            for ($i = 0; $i < $currentLayer->getTotalNeurons(); $i++) { // updating the weights. including the bias neuron
                $currentNeuron->updateWeights($currentLayer->getPreviousLayer());
            }
        }


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
$result  = $network->feedForward([4, 3, 2]);
$network->backProp();