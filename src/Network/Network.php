<?php
namespace Network;
set_time_limit(10);

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
    public $rms = 0.0;

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
                var_dump($inputValues);exit;
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
            $currentNeuron = $outputLayer->getNeurons()[$i];
            $currentNeuron->calculateOutputGradients($expectedValues[$i]);
        }

        // Calculate hidden layer gradients
        for ($i = count($this->getLayers()) - 2; $i > 0; $i--) {
            $currentLayer = $this->layers[$i];
            for ($j = 0; $j < $currentLayer->getTotalNeurons() -1; $j++) {
                $currentNeuron = $outputLayer->getNeurons()[$j];
                $currentNeuron->calculateHiddenGradients($currentLayer->getNextLayer());
            }
        }

        // calculate the new weights
        for ($i = (count($this->getLayers()) - 1); $i > 0; $i--) {
            $currentLayer = $this->layers[$i];
            for ($j = 0; $j < ($currentLayer->getTotalNeurons() - 1); $j++) { // updating the weights. including the bias neuron
                $currentNeuron->updateWeight($currentLayer->getPreviousLayer(), $j);
            }
        }


    }

    /**
     * prints the result
     */
    public function getResult()
    {
        /* @var $currentLayer Layer */
        /* @var $currentNeuron Neuron */
        for ($i = 0; $i < count($this->getLayers()); $i++) {
            $currentLayer = $this->getLayers()[$i];
            for ($j = 0; $j < $currentLayer->getTotalNeurons() -1; $j++) {
                $currentNeuron = $currentLayer->getNeurons()[$j];
                echo 'Neuron ' . $j . ' in layer ' . $i . ':<br />';
                $outputWeights = $currentNeuron->getOutputWeights();
                for ($w = 0; $w < count($outputWeights); $w++) {
                    echo 'Connection' . $w . ' has value' . $outputWeights[$w] . '<br />';
                }
            echo '<br />';
            }
        }
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

$network = new Network([2, 2, 1]);

$trainingSet = [
    [0, 0, 0],
    [0, 1, 1],
    [1, 0, 1],
    [1, 1, 0],
];

for ($i = 0; $i < 200; $i++) {
    if ($i === 0) {
        $previousRms = 1;
    } else {
        $previousRms = $network->rms;
    }
    foreach ($trainingSet as $set) {
        $result = $network->feedForward([$set[0], $set[1]]);
        $network->backProp($set[2]);
//    }
//    if ($network->rms < 0.1 || $network->rms === $previousRms) {
//        // validate trainingsdata
//        foreach ($trainingSet as $set) {
//            $result = $network->feedForward([$set[0], $set[1]]);
            echo 'The result of input values ' . $set[0] . ' and ' . $set[1] . ' equals ' . $result[0] . '<br />';
            echo 'The expected values was: ' . $set[2] . '<br />';
        }
//        break;
//    }
}

