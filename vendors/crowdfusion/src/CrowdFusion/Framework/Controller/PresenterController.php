<?php

namespace CrowdFusion\Framework\Controller;

use Symfony\Component\HttpFoundation\ParameterBag;

class PresenterController extends \AbstractController
{
    /* @var \ApplicationContext */
    protected $applicationContext;

    /* @var string */
    protected $design = 'default';

    /* @var string */
    protected $deviceView = 'main';

    /* @var boolean */
    protected $throwRenderExceptions = true;

    /**
     * @param \ApplicationContext $val
     */
    public function setApplicationContext(\ApplicationContext $val)
    {
        $this->applicationContext = $val;
    }

    /**
     * @param string $design
     */
    public function setDesign($design)
    {
        $this->design = $design;
    }

    /**
     * @param string $deviceView
     */
    public function setDeviceView($deviceView)
    {
        $this->deviceView = $deviceView;
    }

    /**
     * @param bool $val
     */
    public function setThrowRenderExceptions($val = true)
    {
        $this->throwRenderExceptions = $val;
    }

    /**
     * Loads a presenter, calls its method and sets the resulting string into
     * the PRESENTER_RESULT template variable.
     *
     * @return array
     * @throws \Exception
     */
    protected function render()
    {
        $presenter = $this->createPresenter($this->getRequiredTemplateVariable('Presenter'));

        try {
            $arguments = $this->getArguments($presenter);
            $result = call_user_func_array($presenter, $arguments);
        } catch(\Exception $e) {
            if ($this->throwRenderExceptions) {
                throw $e;
            }

            $this->Logger->error($e->getMessage() .
                    "\n\nURL: " . \URLUtils::fullUrl() .
                    "\n\nTemplate Vars:\n" . print_r($this->templateVars, true) .
                    (isset($arguments) ? "\n\n$presenter args:\n" . print_r($arguments, true) : '')
                );

            $result = '';
        }

        $this->setTemplateVariable('PRESENTER_RESULT', $result);

        // just return something so the template engine thinks
        // something happened.
        return array(array());
    }

    /**
     * Returns a callable for the given presenter.
     *
     * @param string $presenter A Presenter string
     *
     * @return mixed A PHP callable
     *
     * @throws \InvalidArgumentException When the presenter class does not exist
     */
    protected function createPresenter($presenter)
    {
        if (false === strpos($presenter, '::')) {
            throw new \InvalidArgumentException(sprintf('Presenter must be in the format class|service::method', $presenter));
        }

        list($class, $method) = explode('::', $presenter, 2);
        $presenter = $this->applicationContext->object($class);

        if (!method_exists($presenter, $method)) {
            throw new \InvalidArgumentException(sprintf('Presenter "%s" does not have a "%s" method.', $class, $method));
        }

        return array($presenter, $method);
    }

    /**
     * Returns the arguments to pass to the presenter.
     *
     * @param mixed   $presenter A PHP callable
     *
     * @return array
     *
     * @throws \RuntimeException When value for argument given is not provided
     *
     * @api
     */
    protected function getArguments($presenter)
    {
        if (is_array($presenter)) {
            $r = new \ReflectionMethod($presenter[0], $presenter[1]);
        } elseif (is_object($presenter) && !$presenter instanceof \Closure) {
            $r = new \ReflectionObject($presenter);
            $r = $r->getMethod('__invoke');
        } else {
            $r = new \ReflectionFunction($presenter);
        }

        return $this->doGetArguments($presenter, $r->getParameters());
    }

    /**
     * @param $presenter
     * @param array $parameters
     * @return array
     * @throws \RuntimeException
     */
    protected function doGetArguments($presenter, array $parameters)
    {
        $attributes = $this->getTemplateVariables();
        if (!isset($attributes['deviceView'])) {
            $attributes['deviceView'] = $this->deviceView;
        }

        if (!isset($attributes['design'])) {
            $attributes['design'] = $this->design;
        }

        $arguments = array();

        /*
         * do not pass through the core cft values
         */
        unset($attributes['DataSource']);
        unset($attributes['Presenter']);
        unset($attributes['CacheDuration']);
        unset($attributes['CacheTime']);
        unset($attributes['DEFER']);

        /* @var \ReflectionParameter $param */
        foreach ($parameters as $param) {
            if (array_key_exists($param->name, $attributes) && !empty($attributes[$param->name])) {
                $paramValue = $attributes[$param->name];
                // convert bool strings to real bools
                if (is_scalar($paramValue)) {
                    if ('true' === $paramValue) {
                        $paramValue = true;
                    } elseif ('false' === $paramValue) {
                        $paramValue = false;
                    }
                }
                $arguments[] = $paramValue;
            } elseif ($param->getClass() && $param->getClass()->getName() === 'Symfony\Component\HttpFoundation\ParameterBag') {
                $arguments[] = new ParameterBag($attributes);
            } elseif ($param->isDefaultValueAvailable()) {
                $arguments[] = $param->getDefaultValue();
            } else {
                if (is_array($presenter)) {
                    $repr = sprintf('%s::%s()', get_class($presenter[0]), $presenter[1]);
                } elseif (is_object($presenter)) {
                    $repr = get_class($presenter);
                } else {
                    $repr = $presenter;
                }

                throw new \RuntimeException(sprintf('Presenter "%s" requires that you provide a value for the "$%s" argument (because there is no default value or because there is a non optional argument after this one).', $repr, $param->name));
            }
        }

        return $arguments;
    }
}
