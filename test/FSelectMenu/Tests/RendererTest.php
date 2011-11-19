<?php

namespace FSelectMenu\Tests;

use FSelectMenu\Renderer;
use FSelectMenu\Translator\ArrayTranslator;

class RendererTest extends \PHPUnit_Framework_TestCase
{
    /** @dataProvider getTestRenderData */
    public function testRender($value, $choices, $options, $expect, $translator = null)
    {
        $renderer = new Renderer('utf-8', $translator);
        $result = $renderer->render($value, $choices, $options);

        $expect = str_replace('><', ">\n<", $expect);
        $result = str_replace('><', ">\n<", $result);

        $this->assertSame($expect, $result);
    }

    public function getTestRenderData()
    {
        $valueClass = function($value) {
            return htmlspecialchars('fselectmenu-value-' . \preg_replace('#\s+#', '-', $value), ENT_QUOTES, 'utf-8');
        };
        $out = function($value, $attrs = null, $in = '') use ($valueClass) {
            if (null === $attrs) {
                $attrs = ' class="fselectmenu-style-default fselectmenu fselectmenu-events ' . $valueClass($value) . '" tabindex="0"';
            }
            return '<span'.$attrs.'>' . $in . '</span>';
        };
        $native = function($attrs = null, $in = '') {
            if (null === $attrs) {
                $attrs = ' class=" fselectmenu-native"';
            }
            return '<select'.$attrs.'>'.$in.'</select>';
        };
        $label = function($label = '') {
            if ('' === $label) {
                $label = "\xC2\xA0";
            }
            return '<span class="fselectmenu-label-wrapper"><span class="fselectmenu-label">'.$label.'</span><span class="fselectmenu-icon"></span></span>';
        };
        $opts = function($value, $attrs = null, $opts = '') use ($valueClass) {
            if (null === $attrs) {
                $attrs = ' class=" fselectmenu-options-wrapper fselectmenu-events '.$valueClass($value).'"';
            }
            return '<span'.$attrs.'><span class="fselectmenu-options">' . $opts . '</span></span>';
        };

        return array(
            
            // Empty
            array('', array(), array(),
                $out('', null, $native() . $label() . $opts('')),
            ),

            // A few choices
            array(
                'x',
                array(
                    'a' => 'A',
                    'x' => 'X',
                    'z' => 'Z',
                ), array(),
                $out('x', null, $native(null,
                    '<option value="a" class="fselectmenu-value-a">A</option>'
                    .'<option selected="selected" value="x" class="fselectmenu-value-x">X</option>'
                    .'<option value="z" class="fselectmenu-value-z">Z</option>'
                ) . $label('X') . $opts('x', null,
                    '<span data-value="a" data-label="A" class="fselectmenu-option fselectmenu-value-a">A</span>'
                    .'<span data-value="x" data-label="X" class="fselectmenu-option fselectmenu-selected fselectmenu-value-x">X</span>'
                    .'<span data-value="z" data-label="Z" class="fselectmenu-option fselectmenu-value-z">Z</span>'
                )),
            ),

            // With optgroup
            array(
                'x',
                array(
                    'a' => array(
                        'b' => 'B',
                        'x' => 'X',
                        'z' => 'Z',
                    ),
                ), array(),
                $out('x', null, $native(null,
                    '<optgroup title="a">'
                    .'<option value="b" class="fselectmenu-value-b">B</option>'
                    .'<option selected="selected" value="x" class="fselectmenu-value-x">X</option>'
                    .'<option value="z" class="fselectmenu-value-z">Z</option>'
                    .'</optgroup>'
                ) . $label('X') . $opts('x', null,
                    '<span class="fselectmenu-optgroup">'
                    .'<span class="fselectmenu-optgroup-title">a</span>'
                    .'<span data-value="b" data-label="B" class="fselectmenu-option fselectmenu-value-b">B</span>'
                    .'<span data-value="x" data-label="X" class="fselectmenu-option fselectmenu-selected fselectmenu-value-x">X</span>'
                    .'<span data-value="z" data-label="Z" class="fselectmenu-option fselectmenu-value-z">Z</span>'
                    .'</span>'
                )),
            ),

            // A few choices, value not in choices
            // The label should be the first choice
            array(
                '',
                array(
                    'a' => 'A',
                    'x' => 'X',
                ), array(),
                $out('', null, $native(null,
                    '<option value="a" class="fselectmenu-value-a">A</option>'
                    .'<option value="x" class="fselectmenu-value-x">X</option>'
                ) . $label('A') . $opts('', null,
                    '<span data-value="a" data-label="A" class="fselectmenu-option fselectmenu-value-a">A</span>'
                    .'<span data-value="x" data-label="X" class="fselectmenu-option fselectmenu-value-x">X</span>'
                )),
            ),

            // A few choices with optgroup, value not in choices
            // The label should be the first choice
            array(
                '',
                array(
                    'a' => array(
                        'b' => 'B',
                        'x' => 'X',
                        'z' => 'Z',
                    ),
                ), array(),
                $out('', null, $native(null,
                    '<optgroup title="a">'
                    .'<option value="b" class="fselectmenu-value-b">B</option>'
                    .'<option value="x" class="fselectmenu-value-x">X</option>'
                    .'<option value="z" class="fselectmenu-value-z">Z</option>'
                    .'</optgroup>'
                ) . $label('B') . $opts('', null,
                    '<span class="fselectmenu-optgroup">'
                    .'<span class="fselectmenu-optgroup-title">a</span>'
                    .'<span data-value="b" data-label="B" class="fselectmenu-option fselectmenu-value-b">B</span>'
                    .'<span data-value="x" data-label="X" class="fselectmenu-option fselectmenu-value-x">X</span>'
                    .'<span data-value="z" data-label="Z" class="fselectmenu-option fselectmenu-value-z">Z</span>'
                    .'</span>'
                )),
            ),

            // A few choices, value is empty
            array(
                '',
                array(
                    'a' => 'A',
                    '' => 'X',
                    'z' => 'Z',
                ), array(),
                $out('', null, $native(null,
                    '<option value="a" class="fselectmenu-value-a">A</option>'
                    .'<option selected="selected" value="" class="fselectmenu-value-">X</option>'
                    .'<option value="z" class="fselectmenu-value-z">Z</option>'
                ) . $label('X') . $opts('', null,
                    '<span data-value="a" data-label="A" class="fselectmenu-option fselectmenu-value-a">A</span>'
                    .'<span data-value="" data-label="X" class="fselectmenu-option fselectmenu-selected fselectmenu-value-">X</span>'
                    .'<span data-value="z" data-label="Z" class="fselectmenu-option fselectmenu-value-z">Z</span>'
                )),
            ),

            // Custom class
            array('', array(), array(
                    'attrs' => array(
                        'class' => 'fselectmenu-style-foo',
                    ),
                ),
                $out(null, ' class="fselectmenu-style-foo fselectmenu fselectmenu-events fselectmenu-value-" tabindex="0"', $native() . $label() . $opts('')),
            ),

            // Custom tabindex
            array('', array(), array(
                    'attrs' => array(
                        'tabindex' => '42',
                    ),
                ),
                $out(null, ' class="fselectmenu-style-default fselectmenu fselectmenu-events fselectmenu-value-" tabindex="42"', $native() . $label() . $opts('')),
            ),

            // Options wrapper attrs
            array('', array(), array(
                    'optionWrapperAttrs' => array(
                        'class' => 'foo',
                    ),
                ),
                $out('', null, $native() . $label() . $opts(null, ' class="foo fselectmenu-options-wrapper fselectmenu-events fselectmenu-value-"')),
            ),

            // Escape
            array(
                '<a>',
                array(
                    '<o>' => array(
                        '<a>' => '<A>',
                    ),
                ), array(),
                $out('<a>', null, $native(null,
                    '<optgroup title="&lt;o&gt;">'
                    .'<option selected="selected" value="&lt;a&gt;" class="fselectmenu-value-&lt;a&gt;">&lt;A&gt;</option>'
                    .'</optgroup>'
                ) . $label('&lt;A&gt;') . $opts('<a>', null,
                    '<span class="fselectmenu-optgroup">'
                    .'<span class="fselectmenu-optgroup-title">&lt;o&gt;</span>'
                    .'<span data-value="&lt;a&gt;" data-label="&amp;lt;A&amp;gt;" class="fselectmenu-option fselectmenu-selected fselectmenu-value-&lt;a&gt;">&lt;A&gt;</span>'
                    .'</span>'
                )),
            ),

            // Raw labels
            array(
                'x',
                array(
                    'a' => '<A>',
                ), array(
                    'rawLabels' => true,
                ),
                $out('x', null, $native(null,
                    '<option value="a" class="fselectmenu-value-a">&lt;A&gt;</option>'
                ) . $label('<A>') . $opts('x', null,
                    '<span data-value="a" data-label="&lt;A&gt;" class="fselectmenu-option fselectmenu-value-a"><A></span>'
                )),
            ),

            // Fixed label
            array(
                '',
                array(
                    'a' => 'A',
                ), array(
                    'fixedLabel' => 'foo',
                ),
                $out(null, ' class="fselectmenu-style-default fselectmenu fselectmenu-events fselectmenu-value-" tabindex="0" data-fixedlabel="foo"', $native(null,
                    '<option value="a" class="fselectmenu-value-a">A</option>'
                ) . $label('foo') . $opts('', null,
                    '<span data-value="a" data-label="A" class="fselectmenu-option fselectmenu-value-a">A</span>'
                )),
            ),

            // Option attribs
            array(
                'x',
                array(
                    'a' => 'A',
                    'x' => 'X',
                    'z' => 'Z',
                ), array(
                    'optionAttrs' => array(
                        'a' => array('foo' => 'bar'),
                        'z' => array('bar' => 'foo'),
                    ),
                ),
                $out('x', null, $native(null,
                    '<option value="a" class="fselectmenu-value-a">A</option>'
                    .'<option selected="selected" value="x" class="fselectmenu-value-x">X</option>'
                    .'<option value="z" class="fselectmenu-value-z">Z</option>'
                ) . $label('X') . $opts('x', null,
                    '<span foo="bar" data-value="a" data-label="A" class="fselectmenu-option fselectmenu-value-a">A</span>'
                    .'<span data-value="x" data-label="X" class="fselectmenu-option fselectmenu-selected fselectmenu-value-x">X</span>'
                    .'<span bar="foo" data-value="z" data-label="Z" class="fselectmenu-option fselectmenu-value-z">Z</span>'
                )),
            ),

            // Translation
            array(
                'x',
                array(
                    'a' => array(
                        'b' => 'B',
                        'x' => 'X',
                        'z' => 'Z',
                    ),
                ), array(),
                $out('x', null, $native(null,
                    '<optgroup title="tr_a">'
                    .'<option value="b" class="fselectmenu-value-b">tr_B</option>'
                    .'<option selected="selected" value="x" class="fselectmenu-value-x">tr_X</option>'
                    .'<option value="z" class="fselectmenu-value-z">tr_Z</option>'
                    .'</optgroup>'
                ) . $label('tr_X') . $opts('x', null,
                    '<span class="fselectmenu-optgroup">'
                    .'<span class="fselectmenu-optgroup-title">tr_a</span>'
                    .'<span data-value="b" data-label="tr_B" class="fselectmenu-option fselectmenu-value-b">tr_B</span>'
                    .'<span data-value="x" data-label="tr_X" class="fselectmenu-option fselectmenu-selected fselectmenu-value-x">tr_X</span>'
                    .'<span data-value="z" data-label="tr_Z" class="fselectmenu-option fselectmenu-value-z">tr_Z</span>'
                    .'</span>'
                )),
                new ArrayTranslator(array(
                    'a' => 'tr_a',
                    'B' => 'tr_B',
                    'X' => 'tr_X',
                    'Z' => 'tr_Z',
                )),
            ),

        );
    }
}

