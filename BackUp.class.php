<?php
class BackUp
{ 
	public static function mysqlBackUp($host,$user,$pass,$name,$backUpFolder,$tables = '*')
	{
		error_reporting(0);
		$link = mysql_connect($host,$user,$pass);
		mysql_select_db($name,$link);
		
		//get all of the tables
		if($tables == '*')
		{
			$tables = array();
			$result = mysql_query('SHOW TABLES');
			while($row = mysql_fetch_row($result))
			{
				$tables[] = $row[0];
			}
		}
		else
		{
			$tables = is_array($tables) ? $tables : explode(',',$tables);
		}
		
		//cycle through
		foreach($tables as $table)
		{
			$result = mysql_query('SELECT * FROM '.$table);
			$num_fields = mysql_num_fields($result);
			
			$return.= 'DROP TABLE '.$table.';';
			$row2 = mysql_fetch_row(mysql_query('SHOW CREATE TABLE '.$table));
			$return.= "\n\n".$row2[1].";\n\n";
			
			for ($i = 0; $i < $num_fields; $i++) 
			{
				while($row = mysql_fetch_row($result))
				{
					$return.= 'INSERT INTO '.$table.' VALUES(';
					for($j=0; $j<$num_fields; $j++) 
					{
						$row[$j] = addslashes($row[$j]);
						$row[$j] = ereg_replace("\n","\\n",$row[$j]);
						if (isset($row[$j])) { $return.= '"'.$row[$j].'"' ; } else { $return.= '""'; }
						if ($j<($num_fields-1)) { $return.= ','; }
					}
					$return.= ");\n";
				}
			}
			$return.="\n\n\n";
		}
		
		//save file
		$file=$backUpFolder.'.sql';
		$handle = fopen($file,'w+');
		fwrite($handle,$return);
		fclose($handle);
		return $file;
	}

	
	public static function fileBackUp($dir,$cFolder) { 

	  	ini_set('memory_limit', '-1');
	  	$result = array(); 
	  	$result['file_count']=0;
	  	$result['folder_count']=0;
	  	$result['files']="";
	  	$result['folder']="";

	  	if(!file_exists($cFolder))  mkdir($cFolder);
	  	

		$cdir = scandir($dir);
		if(!file_exists($cFolder. $dir))  mkdir($cFolder. $dir);

		foreach ($cdir as $key => $value) 
		{ 
			if (!in_array($value,array(".","..")) && !strpos($dir . DIRECTORY_SEPARATOR . $value,"site_backup")) 
			{ 
			   if (is_dir($dir . DIRECTORY_SEPARATOR . $value)) 
			   { 
			      	$result['folder_count']=$result['folder_count']+1;
			      	$result['folder']=$result['folder']."##". $value;
			      	$result[$value] = self::fileBackUp($dir . DIRECTORY_SEPARATOR . $value,$cFolder);			      
			   } 
			   else 
			   { 
			      	$result[] = $value;
			      	$result['file_count']=$result['file_count']+1;
			      	$result['files']=$result['files']."##". $value;
			      	$content=file_get_contents($dir . "/" . $value);
			     	file_put_contents($cFolder .$dir . "/" . $value, $content);
			   } 
			} 
		} 

		return $result; 
	}
}
