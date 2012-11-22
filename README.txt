#The drupal 7 implementation of TWIG template language.

##Updated 22-11-2012 for version 7.0.2.0

Beware this version is not 100% downards compatible with the previous version.


##Changes
- Compiled templates are now stored in a private folder. Be sure to set one at
  admin/config/media/file-system
- Removed the old custom function call node as the functionallity is now 
  available native in TWIG
- Rebuild the way templates are discovered and internally stored.
- Added drupal_static construction to the disk expensive calls.
- Moved the autoloader into the project, patching of the config is not needed.


##General information and installation

For more information about Twig, visit http://twig-project.org

To use this engine you will need to clone Twig from git
(https://github.com/fabpot/Twig.git) and manually copy the contents of /lib/Twig 
into sites/all/libraries in a folder called Twig

The contents of TFD from this module needs to be moved into libraries as well.

So you end up with a folder structure like this.

sites/all/libraries/Twig/
sites/all/libraries/Twig/Error/
sites/all/libraries/Twig/Extension/ ... etc
sites/all/libraries/TFD/Autoloader.php
sites/all/libraries/TFD/Loader/  ... etc


Then move the twig.engine file into ./themes/engines/twig/ so that is on the 
same level as the phptemplate engine drupal comes with.


##Usage

To create a theme for twig, simply set engine = twig in your theme.info and 
start creating templates


##Drupal specific extensions

Besides the Default twig functions, I've added some extra's specific for this 
drupal implementation.

###Autorender

This version of the TWIG engine uses auto render to prevent themers get RSI 
from typing {{node.field_somefield|render}} for every single field they want to
render from the render array (of doom) so the can safely type 
{{node.field_something}} 

On rendering of the compiled template TFD check if the called variable is a 
string, callable or array. If it's a string it simple does echo $string, if it's
a callable it return a proper method() for it.
And if it's an array, it assumes it's a renderable array and maps it to the 
render($string); method of drupal.

This way the objects hidden with hide() are respected.

To turn this is for now, you have to set autorender to FALSE in the
twig_get_instance() method of twig.engine.

I'am working on a {% noautorender %} {% end noautorender %} block structure.


###theme_get_setting

{% if theme_get_setting('my_theme_setting') %}
   do something
{% endif %}

or 

print the value of the setting
{{  theme_get_setting('my_theme_setting_value') }}


###Extending templates
For Drupal, the twig engine is aware of all the templates in your theme, and if it is a sub theme the engine know the templates of the base theme.

syntax : {% extend 'theme::path:to:template.tpl.twig' %} or {% 'theme::template.tpl.twig' %} or {% extend 'template.tpl.twig' %}

Extending a template from the current theme
{% extends 'block.tpl.twig' %}
or
{% extends 'theme::block.tpl.twig' %}

Extending a template from the base theme is basically the same call.

Say your base theme is mothership (and yes there is a twig version of that, not yet public ;))

{% extends 'mothership::block.tpl.twig' %}

Same goes for macro's or includes.

{% import "mothership::templates:includes:macro.renderitems.tpl.twig" as render %}


###with
With allows a scope-shift of an array into a defined array.
{% with expr [as localName] [,expr2 [as localName2], [.]]  {sandboxed|merged} %}
	..Do Stuff..
{% endwith %}

For a more detailed example see /engines/twig/lib/TFD/TokenParser/With.php

###switch
gives you a php like switch system

	{% switch page.regions %}
		{%case 'header %}
			{% include 'header.tpl.html'}
			{# no break needed, as this default #}
		{%case 'content' fallthrough %}
			{# do something and fall trough to next case #}
	{% endswitch %}



###dump

A filter that dumps the variable instead of printing it. If the method is 
ommited, the filter first checks if the devel module is activated and if so
a DPM is issued. If not, a var_dump is called

the optional parameters are : var_dump or v, print_r or p,  dpr, dpm

{{ varname|dump(param) }}


###url

maps to the drupal url() function
{{node.nid|url}}

###image_url
This maps an image cache preset to a fid

{{node.fid|image_url('preset')}}

###t
This maps to the drupal t() method
{{'Download'|t}} or {{'Download'|t('nl')}}

###image_size
Returns either an array with the width and height of a given image.
{{ node.fid|image_size('preset',asHTML) }}
If asHTML is true, then a HTML string is returned else an array with the
elements.

###render and hide
Drupal render() is available as a filter and function

	{{page.footer|render}}
	{{page.header.form.search|render}}

	{% render(content.field) %}

Same goes for hide, {% hide(content.comments) %} or {{ content.comments|hide }}

The filter version might be removed in the future, as it is not really adding any value.


###Theme
Although the usage is highly discusable, the theme function is availabe as
function call in twig. But in general, use render instead of theme!

{% theme('hook',variables) %} 

###Adding your own  filters 

You can expand twig with your own filters, by implementing
hook_twig_filters(&$filters) in your own module

	my_module_twig_filters(&$filters){
		$filters['foo'] = new Twig_Filter_Function('my_module_twig_filter_foo');
	}

	function my_module_twig_filter_foo($var){
		return $var .' with foo!';
	}
	
Or just map it to an php function
	my_module_twig_filters(&$filters){
		$filters['rot13'] = new Twig_Filter_Function('str_rot13');
	}


### Adding your own functions ###

You can expand twig with your own custom functions, by implementing 
hook_twig_function(&$functions) in your own module


  $functions['rebuild'] = new Twig_Function_Function('registry_rebuild');

and then do a {% rebuild %} in your code.


##More to come.. 


YES it's that easy to start using twig in D7, so what are you waiting for.. 

get Twiggy with it :-)



	

