# Library Inclusion

A Library is a file, or set of files that live outside of the sites source
files but can be made available for use by the site. Library inclusion
consists of three steps, linking, compilation and inclusion.

## Linking

Linking is the process of making a library's files available in the document
root so that they can accessed from the web. Linking is a necessary step for
inclusion.

## Compilation

Compilation is the process of compressing the library's files in a manner which
improves performance. Compilation is optional for inclusion. Compilation can be
resource and time intensive and should generally not happen while processing a
page request. It should instead be performed prior to the site processing any
requests durring a dedicated compile step of deployment.

## Inclusion

Inclusion is the process of adding the library's files to a document so that the
functionality provided by the library can be offered to the user.

