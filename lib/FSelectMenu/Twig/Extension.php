<?php

namespace FSelectMenu\Twig;

use FSelectMenu\Renderer;

class Extension extends \Twig_Extension
{
    private $renderer;

    public function getFunctions() {
        return array(
            'fselectmenu' => new \Twig_Function_Method($this, 'fselectmenuFunction', array('needs_environment' => true)),
        );
    }

    public function fselectmenuFunction($env, $value, array $choices, array $options)
    {
        if (null === $renderer = $this->renderer) {
            $renderer = $this->renderer = new Renderer($env->getCharset());
        }

        return new \Twig_Markup($renderer->render($value, $choices, $options));
    }

    public function getName()
    {
        return 'fselectmenu';
    }
}

function fselectmenu_value_class($value)
{
    return preg_replace('#[^\w.-]+#', '-', $value);
}
