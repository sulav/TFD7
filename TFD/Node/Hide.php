<?php
/**
 * Class TFD_Node_Hide

 * Transports the variable to be passed to the hide method into a variable
 * before calling the hide method. Prevents strict warnings.
 *
 * Extends a Twig_Node_Print for a fluent interface.
 * So developers can do a {{ hide(this)}} instead of {% hide(this) %}
 *
 */

class TFD_Node_Hide extends Twig_Node_Print
{


    public function compile(Twig_Compiler $compiler)
    {
        $compiler
            ->addDebugInfo($this)
            ->write("\$_temp=")->subcompile($this->getNode('expr'))->raw(";")
            ->raw("tfd_hide(\$_temp);")
            ->raw("unset(\$_temp);\n");

    }
}
 
