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

    /**
     * @var float
     */
    protected $gradient;

    /**
     * @var float
     */
    protected $learningRate = 0.1;

    /**
     * @var float
     */
    protected $alpha = 0.5;

    /**
     * @var string
     */
    protected $name = '';

    /**
     * Neuron constructor.
     * @param int $numberOfNeuronsNextLayer
     * @param $layer
     * @param $type
     * @param $position
     */
    public function __construct($numberOfNeuronsNextLayer, $layer, $type, $position)
    {
        for ($i = 0; $i < $numberOfNeuronsNextLayer; $i++) {
            $this->outputWeights[] = mt_rand(1, 10) / 10;
        }
//        echo 'created a ' . $type . ' neuron on layer ' . $layer . ' on position ' . $position . '<br />';
        $this->setName($type . 'neuron-' . $layer . '-pos-' . $position);
//        echo 'created: ' . $this->getName() . '<br />';
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
            $sum           += $currentNeuron->getValue() * $this->getOutputWeight($currentNeuron, $positionInLayer);
        }
        $this->setValue($this->activationFunction($sum));
    }

    /**
     * @param int $expectedValue
     */
    public function calculateOutputGradients($expectedValue)
    {

        $difference     = $expectedValue - $this->getValue();
        $this->setGradient($difference * $this->activationDerivativeFunction($this->getValue()));
    }

    /**
     * @param Layer $nextLayer
     * @return float
     */
    public function calculateHiddenGradients(Layer $nextLayer)
    {
        $errorDifference = $this->getErrorDifference($nextLayer);
        $this->setGradient($errorDifference * $this->activationDerivativeFunction($this->getValue()));
    }

    /**
     * @param Layer $nextLayer
     * @return double
     */
    private function getErrorDifference(Layer $nextLayer)
    {
        $sum = 0;
        for ($i = 0; $i < count($this->outputWeights); $i++) {
            /* @var $nextLayerNeuron Neuron*/
            $nextLayerNeuron = $nextLayer->getNeurons()[$i];
            $sum += $this->outputWeights[$i] * $nextLayerNeuron->getGradient();
        }
        return $sum;
    }

    /**
     *
     * Updates the weight of a single connection.
     *
     * @param Layer $previousLayer
     * @param $currentPosition
     * @return bool
     */
    public function updateWeight(Layer $previousLayer, $currentPosition)
    {
        /* @var $previousLayerNeuron Neuron*/

        for ($i = 0; $i < count($previousLayer->getNeurons()) -1; $i++) { // include the bias neuron
            $previousLayerNeuron = $previousLayer->getNeurons()[$i];
            $oldWeight           = $previousLayerNeuron->outputWeights[$currentPosition];
            $newWeight           = $this->learningRate * $previousLayerNeuron->getValue() * $this->getGradient() +
                                   $this->alpha * $oldWeight;
            $previousLayerNeuron->outputWeights[$currentPosition] = $newWeight;

//            echo $this->getName();
//            echo '<br />';
//            echo $this->learningRate;
//            echo '<br />';
//            echo $previousLayerNeuron->getValue();
//            echo '<br />';
//            echo $this->getGradient();
//            echo '<br />';
//            echo $this->alpha;
//            echo '<br />';
//            echo $oldWeight;
//            echo '<br />';
//            echo $newWeight;
//            echo '<br />';

        }
        return true;
    }

    /**
     * @return array
     */
    public function getOutputWeights()
    {
        return $this->outputWeights;
    }

    /**
     * @param Neuron $previousNeuron
     * @param $currentNeuronPosition
     * @return float
     */
    protected function getOutputWeight(Neuron $previousNeuron, $currentNeuronPosition)
    {
        return $previousNeuron->getOutputWeights()[$currentNeuronPosition];
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

    /**
     * @param float $x
     * @return float
     */
    function activationDerivativeFunction($x)
    {
        return 1 / (cosh($x) * cosh($x));
    }

    /**
     * @param float $x
     * @return float
     */
    function activationFunction($x)
    {
        return tanh($x);
    }


    /**
     * @return float
     */
    public function getGradient()
    {
        return $this->gradient;
    }

    /**
     * @param float $gradient
     * @return Neuron
     */
    public function setGradient($gradient)
    {
        $this->gradient = $gradient;
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
     * @return Neuron
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }
}