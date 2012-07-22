artificial-stupidity
====================

This is a buggy example of genetic programming. Or, well, no, it isn't.
It's an example of generating code with a PRNG.

Usage
=====

Start it with:

    php main.php

It's interactive after that. If you see `human >>>` that's where you
should type your questions. The program will try to answer them, based
on its current knowledge. If the answer is wrong, you can type in a
correction. It will then try to extend its knowledge to include your
question-correct-answer pair.

A very basic "brain" (`knowledge.php.dump`) is included. Try "1+1" as
a question, for example.

Exit with `Ctrl-c`.

Background
==========

On each point where it didn't have the correct answer, the program randomly
generates Brainfuck code that takes your question as an input and returns
the answer (or, at least it tries - it might time-out in-between). This
part of "knowledge" - the Brainfuck program that gives the right answer to
exactly your question - is then put into a big hash-table (which is dumped
into `knowledge.php.dump`). It's all very simple, thought the code is not
exactly very readable.

Related blog post: <http://blog.r-wos.org/2011/artificial-stupidity>

Author
======

Richard Wossal <richard@r-wos.org>

