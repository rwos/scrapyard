gcc-street
==========

It's basically a graphical compiler-error-message viewer.
A very quick hack.

![Screenshot](http://r-wos.org/media/gccstreet.png)

Preparation
===========

You'll need `pygame`, a python game library. On debian systems do:

    apt-get install python-pygame

Additionally, you'll need something that throws out errors with the word
"error" in them, for example gcc.

Usage
=====


Pipe a GCC compilitation's error output into it, like so:

    gcc hello.c 2>&1 | ./gccstreet

For your convenience, a C file with errors (`hello.c`) is included in the
distribution.

Background
==========

I once discussed the user-unfriendliness of compiler error messages with
my colleagues and this quick hack is the result. "They should be graphical,
showing funny little animations"...

Author
======

Richard Wossal <richard@r-wos.org>

