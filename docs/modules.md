# Modules

Modules are optional components of conductor.  The difference with a modules and
conductor PHP code that is simply not used is that a module depends on some
database structure being present.  For sites that don't want to use the module
the sql should not be installed meaning the sql can't be part of the core sql
and alters.
