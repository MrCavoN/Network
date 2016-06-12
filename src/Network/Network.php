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
                    if ($j === $topology[$i]) {
                        $currentLayer->addNeuron(new Neuron($topology[$i+1], $i, 'bias', $topology[$i]));
                    } else {
                        $currentLayer->addNeuron(new Neuron($topology[$i+1], $i, 'normal', $j));
                    }
                } else {
                    if ($j === $topology[$i]) {
                        $currentLayer->addNeuron(new Neuron(0, $i, 'bias', $topology[$i]));
                    } else {
                        $currentLayer->addNeuron(new Neuron(0, $i, 'normal', $j));
                    }
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
//                    echo 'value of ' . $currentNeuron->getName() . ' is ' . $currentNeuron->getValue() . '<br />';
                }
            }

            /* stopcondition */
            if ($currentLayer->getNextLayer() === null) {
                return $currentLayer->getNeuronValuesAsArray();
            }
        }
    }

    /**
     * @param array $expectedValues
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
                $currentNeuron = $currentLayer->getNeurons()[$j];
                $currentNeuron->calculateHiddenGradients($currentLayer->getNextLayer());
            }
        }

        // calculate the new weights
        for ($i = (count($this->getLayers()) - 1); $i > 0; $i--) { // i = 2
            $currentLayer = $this->layers[$i];
//            echo '<pre>'; var_dump($currentLayer->getNeurons());exit;
            for ($j = 0; $j < ($currentLayer->getTotalNeurons() - 1); $j++) {
                $currentNeuron = $currentLayer->getNeurons()[$j];
                // updating the weights. including the bias neuron of the previous layer
//                echo $currentNeuron->getName() . '<br />';
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
            for ($j = 0; $j < $currentLayer->getTotalNeurons(); $j++) {
                $currentNeuron = $currentLayer->getNeurons()[$j];
                echo $currentNeuron->getName() . '<br />';
                $outputWeights = $currentNeuron->getOutputWeights();
                for ($w = 0; $w < count($outputWeights); $w++) {
                    echo 'Connection' . $w . ' has value' . $outputWeights[$w] . '<br />';
                }
            echo '<br />';
            }
        }

        echo '<br />=========================================<br />';
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

$network = new Network([2, 4, 2]);
$network->getResult();
$trainingSet = [
    [0, 0, 1, 1],
    [0, 1, 1, 0],
    [1, 0, 0, 0.5],
    [1, 1, 0, 0],
];

$successRms = 0;
$maxIterations = 5000;
for ($i = 0; $i < $maxIterations; $i++) {
    foreach ($trainingSet as $set) {
        $result = $network->feedForward([$set[0], $set[1]]);
        $network->backProp([$set[2], $set[3]]);
        if ($network->rms < 0.05) {
            $successRms++;
        } else {
            $successRms = 0;
        }

        if ($successRms > 4 || $i > $maxIterations - 4) {
            echo 'The result of input values ' . $set[0] . ' and ' . $set[1] . ' equals ' . $result[0] . 'and'. $result[1] .'<br />';
            echo 'The expected values was: ' . $set[2] . 'and' . $set[3] . '<br />';
            echo 'The network rms was' . $network->rms . '<br />';
        }
    }
    if ($successRms > 8) {
        echo '**********************<br />';
        echo 'Training succesfull in ' . ($i - 8) . 'iterations<br />';
        echo '**********************<br />';
        break;
    }
    if ($i === $maxIterations - 1) {
        echo 'Failed Training the network';
    }
}
$network->getResult();



