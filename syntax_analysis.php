<?php

function filter_file($filter, $file, $descr) {
	if (empty($filter)) {
		echo "No filters found\n";
		exit;
	}

	if (empty($file)) {
		echo "No filters found\n";
		exit;
	}

	$content = file_get_contents($file);

	if ($content === false) {
		echo "Unable to read file: $file\n";
		exit;
	}

	preg_match_all($filter, $content, $matches, PREG_OFFSET_CAPTURE);

	if (!empty($matches)) {
		echo "--------------------------\n";
		echo $file . "\n";
	
		if (!empty($descr)) {
		    echo "--------------------------\n";
		    echo $descr . "\n";
		}
	
		echo "--------------------------\n";
	
		$lines = explode("\n", $content);
	
		foreach ($matches[0] as $match) {
			$position = $match[1];
			$line_number = 0;
			$char_count = 0;
	
			foreach ($lines as $index => $line) {
				$char_count += strlen($line) + 1;

				if ($char_count > $position) {
					$line_number = $index + 1;
					break;
				}
			}
	
			echo "Line " . $line_number . ": " . $match[0] . "\n";
		}
	
		echo "--------------------------\n\n";
	}
}

function process_files() {
	$files = glob('/root/*');

	foreach ($files as $file) {
		if (!preg_match('/\.(php|inc)$/', $file)) {
			continue;
		}

		filter_file('/(;|})*[\n]{3,}/', $file, 'Duas linhas em branco ou mais');
		filter_file('/(?<=^|\s)(if|for|foreach|while|switch|elseif)\(/', $file, 'Acao sem espaco na decisao');
		filter_file('/\)\{/', $file, 'Final de decisao sem espaco');
		filter_file('/;([ ]|\t)+/', $file, 'Pegar se no fim da linha nao tem espaco em branco com tab ou espaco');
		filter_file('/^$(\n\s*\n)+?(\s*)return/', $file, 'Pegar se no return tem várias linhas em branco ou mais antes do return');
		filter_file('/return\s*[^;]*;\s*(\n\s*){2,}/', $file, 'Caso o return tenha uma linha em branco apos ele antes de fechar uma funcao');
		filter_file('/(\/\/|#|\/\*)/', $file, 'Identificar comentarios');
		filter_file('/\}?(\s*)else if?(\s*)\{/', $file, 'Buscar se tem else if');
		filter_file('/function(\s+)(\w+)?([ ]*)\([\w+\$|, \n]*\)?([ |\n]*)\{(\n\s*\n)/', $file, 'Buscar os functions que tenham uma linha em branco abaixo');
		filter_file('/(\&\&|\|\|)([ ])/', $file, 'Identificar ifs que tenham mais de uma decisao em inline');
		filter_file('/^(?!\s*(if|for|foreach|while|switch|elseif)\s*\().*\s\(/', $file, 'Identificar funcoes com espaco ate o (');
		filter_file('/\)[ ]{1,}:/', $file, 'Identificar ) :');
		filter_file('/\[(.*,.+)\]/', $file, 'Identificar array inline');
		filter_file('/array\((.*,.+)\)/', $file, 'Identificar array inline');
	}
}

process_files();
