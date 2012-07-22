<?php
$knowledge = file_exists('knowledge.php.dump')
	? unserialize(file_get_contents('knowledge.php.dump')) : array();;

define('OUT_TEST_SUCCESS', ' ah!');
define('OUT_TEST_FAILED', '');
define('OUT_TIME_KILL', '.');
define('OUT_MEM_KILL', ' eh');

define('TIME_MAX', 0.01);
define('THINKING_TIME_MAX', 20);
define('MEM_MAX', 1024 * 1024 * 1);

for (;;) {

	out("human >>> ");
	$input = in_line();
	$brain = get_brain($input);
 	$given_output = process($input, $brain);
	echo "stupy >>> $given_output\n\thuman correction >>> ";
	$correction = in_line();
	if (strlen($correction) <= 0 || $correction == $given_output) {
		echo "\t\t--> right answer\n";
		/* right answer */
		save_knowledge($input, $given_output, $brain);
		continue;
	}
	/* wrong answer */
	out("stupy >>> hm");

	/* check current brains routines */
	$brain = find_brain($input, $correction);
	if (strlen($brain) > 0) {
		save_knowledge($input, $correction, $brain);
		out(" I knew it!\n");
		goto gen_exit;
	}
	out(' no, wait...');
	
	/* try to genarate a brain that can handle the input->output */
	$thinking_end_time = microtime(true) + THINKING_TIME_MAX;
	for ($f = 0; /* infinite */; $f += 1) { // max mutations
		if (microtime(true) >= $thinking_end_time) {
			break;
		}
		$brain = '';
		for ($i = 0; $i < $f; $i++) {
			$pos = rand(0, strlen($brain) - 1);
			$brain = insert_op($pos, $brain);
			if (test_brain($input, $correction, $brain)) {
				save_knowledge($input, $correction, $brain);
				out(" Ok, got it!\n");
				goto gen_exit;
			}
		}
	}
	out(" uh, I'm too stupid\n");
	gen_exit:
}

function get_brain($input)
{
	global $knowledge;
	if (isset($knowledge[$input])) {
		return $knowledge[$input];
	} else {
		if (!empty($knowledge)) {
			// find lowest distance
			$best = '';
			$best_error = 1000000; // random high number
			foreach ($knowledge as $saved_in => $brain) {
				$error = levenshtein($saved_in, $input);
				if ($error < $best_error) {
					$best_error = $error;
					$best = $brain;
				}
				// is error == 0 --> return that
			}
			// TODO: if error < 1
				return $best;
			// TODO: else -> kombine knowledge and find
				// distance
			// wiki possible combinations
		} else {
			return '';
		}
	}
}

function find_brain($input, $output)
{
	global $knowledge;
	foreach($knowledge as $brain) {
		if (test_brain($input, $output, $brain)) {
			return $brain;
		}
	}
	return '';
}

function save_knowledge($input, $correction, $brain)
{
	global $knowledge;
	$knowledge[$input] = $brain;
	file_put_contents('knowledge.php.dump', serialize($knowledge));
}

function test_brain($input, $output, $brain)
{
	if (!valid_brain($brain)) {
		return false;
	}
	$brain_output = process($input, $brain);
	if ($brain_output !== $output) {
		out(OUT_TEST_FAILED);
		return false;
	}
	out(OUT_TEST_SUCCESS);
	return true;
}

function valid_brain($brain)
{
	if (strlen($brain) == 0)
		return false;
	$left_c = substr_count($brain, '[');
	$right_c = substr_count($brain, ']');
	if ($left_c !== $right_c)
		return false;
	return true;
}

function insert_op($pos, $brain)
{
	$ops = "><+-.,[]";
	$left = substr($brain, 0, $pos);
	$right = substr($brain, $pos);
	return $left . $ops[rand(0, strlen($ops)-1)] .  $right;
}

function out($s) { echo $s; }
function in_line() { return trim(fgets(STDIN)); }

///////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////

function brainfuck_interpret(&$source, &$src_p, &$memory, &$mem_p,
&$input, &$in_p, &$output, $stoptime)
{
	do {
		if (microtime(true) >= $stoptime) {
			out(OUT_TIME_KILL);
			return false;
		}
		switch($source[$src_p]) {
		case '+':
			$memory[$mem_p] = chr(ord($memory[$mem_p]) + 1);
			break;
		case '-':
			$memory[$mem_p] = chr(ord($memory[$mem_p]) - 1);
			break;
		case '>':
			$mem_p++;
			if (! isset($memory[$mem_p])) {
				$memory[$mem_p] = chr(0); /* auto-grow memory */
				if ($mem_p > MEM_MAX) {
					out(OUT_MEM_KILL);
					return false; 
				}
			}
			break;
		case '<':
			$mem_p--;
			if ($mem_p < 0)
				return false;
			break;
		case '.':
			$output .= $memory[$mem_p];
			break;
		case ',':
			if ($in_p >= strlen($input))
				$memory[$mem_p] = chr(0);
			else
				$memory[$mem_p] = $input[$in_p++];
			break;
		case '[':
			if (ord($memory[$mem_p]) != 0) {
				/* execute loop */
				$loop_entry_src_p = $src_p - 1;
				$src_p++;
				$loop_return = brainfuck_interpret(
					$source, $src_p, $memory, $mem_p,
					$input, $in_p, $output, $stoptime);
				if ($loop_return === true) {
					// execute again
					$src_p = $loop_entry_src_p;
				} else if ($loop_return === false) {
					// some error, bail out
					return false;
				}
			 } else {
				/* skip loop */
				$brackets = 1;
				while($brackets != 0) {
					$src_p++;
					if ($src_p >= strlen($source) - 1)
						return false;
					if($source[$src_p] == '[')
						$brackets++;
					else if($source[$src_p] == ']')
					       $brackets--;
				}
			 }
			 break;
		case ']':
			if (ord($memory[$mem_p]) != 0) {
				return true;
			} else {
				return 0;
			}
		}
	} while(++$src_p < strlen($source));
}

/* Call this one in order to interpret brainfuck code */

function process($input, $brain)
{
	if (strlen($brain) <= 0) {
		return false;
	}
	$memory         = array();
	$memory[0]      = chr(0);
	$memory_index   = 0;
	$source_index = 0;
	$input_index  = 0;
	$output       = '';
  
  /* Call the actual interpreter */
	brainfuck_interpret(
		$brain, $source_index,
		$memory,   $memory_index,
		$input,  $input_index,
		$output, microtime(true) + TIME_MAX
	);
      
  return $output;
}

