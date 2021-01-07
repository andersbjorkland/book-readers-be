<?php


namespace App\Utility;


class ProdStructure {

	/**
	 * This will recursively find sub-folders and move its contents from a folder source to a target destination.
	 * It will not remove the original folder-structure, but it will move their content.
	 *
	 * Credit to https://gist.github.com/baamenabar/f0ee62fd42fed31b60ce for basic structure of move-logic.
	 */
	public static function movePublicContent($args, $folder="./public/", $targetSubfolder="")
	{
		// move to public folder if on PROD server.
		$src = '' . $folder;

		// Assumed present location is at /domain.com/httpd.private/folder/
		$target = '../../httpd.www/' . $targetSubfolder;


		if (is_dir($src) && is_dir($target)) {
			if ($handle = opendir($src)) {
				while (($file = readdir($handle)) !== false) {

					if ($file==".") continue;
					if ($file=="..")continue;

					if (is_dir("$src/$file")) {
						if (!is_dir("$target/$file")) {
							mkdir("$target/$file");
						}
						self::movePublicContent($args, "$src/$file", "$targetSubfolder/$file");

					} else {
						echo "$src/$file -> $target/$file: ";
						if (rename("$src/$file", "$target/$file")) {
							echo "SUCCESS \n";
						} else {
							echo "FAILED \n";
						}
					}
				}

				closedir($handle);
			}
		} else {
			if (!is_dir($src)) echo "$src is NOT a valid directory!";
			if (!is_dir($target)) echo "$target is NOT a valid directory!";
		}

	}

	public static function moveStagingContent($args, $folder="./public/", $targetSubfolder="")
	{
		// move to public folder if on PROD server.
		$src = '' . $folder;

		// Assumed present location is at /domain.com/httpd.private/folder/
		$target = '../../httpd.www/staging/' . $targetSubfolder;


		if (is_dir($src) && is_dir($target)) {
			if ($handle = opendir($src)) {
				while (($file = readdir($handle)) !== false) {

					if ($file==".") continue;
					if ($file=="..")continue;

					if (is_dir("$src/$file")) {
						if (!is_dir("$target/$file")) {
							mkdir("$target/$file");
						}
						self::movePublicContent($args, "$src/$file", "$targetSubfolder/$file");

					} else {
						echo "$src/$file -> $target/$file: ";
						if (rename("$src/$file", "$target/$file")) {
							echo "SUCCESS \n";
						} else {
							echo "FAILED \n";
						}
					}
				}

				closedir($handle);
			}
		} else {
			if (!is_dir($src)) echo "$src is NOT a valid directory!";
			if (!is_dir($target)) echo "$target is NOT a valid directory!";
		}

	}

}