Code Inflector
====

A command to inflect and replace strings on text content or files. 

## General Usage

Command usage:

    bin/inflector inflect:<type> [<options>] <path>

### Inflection types:

* File
* Class
* Entity
* Template
* Bundle

### Command Options:

The following options are valid to all inflection types:

* `--mode`: The inflection mode. The default value is `1` (camelize). Allowed values: 
	* `1`- Transform any **ClassiFy** or **table-ized** in **classiFy** and **tableIzed**
	* `2`- Transform any **ClassiFy** or **camelCase** format in **classi-fy** and **camel-case**
	* `3`- Transform any **table-ized** or **camelCase** format in **TableIzed** and **CamelCase**
* `--restore`: Restore original from ~.backup before inflected
* `--save`: The save file mode. The default behavior is not save file. Available values: 
	* `0` - Save the content to a file with the same name with `~.preview` extension. 
	* `1` - Save the content to backup file with `~.backup` extensions before overwrite original file
	* `2` - Overwrite original file without save a backup copy file. 
* `--preview`: Show a preview to each inflected file
* `--force`: Force inflection, restore and save without ask confirmation.

## Inflection Type Usage


### File:

Inflect the content of a file. 

    bin/inflector [<options>] inflect:file <file>
    
Available options:

* `--var`: An array of variables to inflect and replace with inflected value

Example:

	bin/inflector inflect:file ./tmp/file.txt --var=foo_bar --var=bar_foo --var=baz_bar

This will output: Content with **fooBar** and **barFoo** and **bazBar** (with Camelize as default inflection mode).
    
    
### Class

Inflect the content of class. Only attributes and it usage are inflected. The methods and some code outside of class are preserved. Usage:

	bin/inflector [<options>] inflect:class <class-namespace>	
Example:

	bin/inflector inflect:class ./tmp/TestInflect
	
	
### Entity

Inflect the content of entity class based on your YAML mapping. The inflector parses the YAML file and load the class inflector. The inflection overwrite the content of both files (class and mapping). Attributes on entity class, mapped fields and relationship field names area inflected, as the `indexBy`, `orderBy`, `mappedBy` and `inversedBy` relation config. Usage:

	bin/inflector [<options>] inflect:entity <entity-file>	
Example:
	
	bin/inflector inflect:entity ./tmp/TestEntity.orm.yml
		

### Template

Inflect the content of a Twig template. The inflector parse the Twig template searching by variables that could be inflected. Usage:
	
	bin/inflector [<options>] inflect:template <template-file>
	
Example:

	bin/inflector inflect:template ./tmp/view.html.twig	

### Bundle

Inflect the content of all files of a Symfony Bundle, using the inflection types: Class, Entity and Template. Your usage is recommended only under a Symfony installation, as Composer dependency, to autoload classes and entities properly. Usage:

	bin/inflector [<options>] inflect:bundle <bundle-path>
	
Available options:

* `--namespace`: Optional bundle namespace. If not provided, the command calculate it based on Bundle path. 

Example:

	bin/inflector inflect:bundle ./tmp/TestBundle --namespace="TestBundle"
	
To inflect entities, it must be necessary provide the Doctrine mapping path. The command automatically predict the path to default Symfony Bundle Doctrine mapping: `<Bundle>/Resources/config/doctrine`. The Twig templates path must be confirmed too, as a default value: `<Bundle>/Resources/views`.	