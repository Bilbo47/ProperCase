<?

# Rename specified folder or file
#   BaseName  becomes ProperCase only if it is all-uppercase or all-lowercase
#   Extension becomes UPPERCase, except for certain cases
#
## Scheme: massage input name to desired output name
##   If they        match then No Change
##   If they do not match then Rename

include 'C:\Src\PHP\Inc\incGeneric.PHP';

$NamNew = ''; # set by NamExtNewGet
$ExtNew = ''; # set by NamExtNewGet

function DateTimeStampFormatted($Str) {                       # i_f Src is 14 Digits then change DateTime-ify the format
  # 20151116135138
  # 123456789 1234
  # YYYYMMDDHHmmSS

  # YYYY-MM-DD HH-mm-SS
  # 2015-11-16 13-51-38

  $DatTimMin = 20100101000000; # 2010-01-01 00-00-00 Min      # Date Range
  $DatTimMax = 49991231235959; # 4999-12-31 23-59-59 Max      #

  if (                                                        # see whether this is
       ( strlen($Str) == 14 )                                 #   a valid DateTime
     & ( $Str >= $DatTimMin )                                 #   We assume a non-digit will make it NOT a valid DateTime
     & ( $Str <= $DatTimMax )
     )
  {
    $Str = substr($Str, 0, 4) . '-' .
    $Str = substr($Str, 4, 2) . '-' .
    $Str = substr($Str, 6, 2) . ' ' .
    $Str = substr($Str, 8, 2) . '-' .
    $Str = substr($Str,10, 2) . '-' .
    $Str = substr($Str,12, 2)         ;
  }
  return $Str;
} // DateTimeStampFormatted
function HyphensToSpaces($Str) {
  $bolHasSpaces = strpos($Str, ' ');                          # get whether we have spaces
  if (! $bolHasSpaces) {                                      # there are no spaces so continue
    $Str = str_replace('-', ' ', $Str);                       # convert hyphens to spaces
  }
  return $Str;
} // HyphensToSpaces
function ExtIsException($strExt) {                            # return whether this Extension should be lowercased or UPPERCased
  # ToDo: scan through an array of exceptions, looking for a match
  $bolEqu = strcasecmp($strExt, '7z');                        # < 0 if str1 is less    than str2; > 0 if str1 is greater than str2; 0 if they are equal

  $bolRtn = ($bolEqu == 0);                                   # Zero => True; Non-Zero => False;
  return $bolRtn;
} # ExtIsException
function NamExtNewGet($NamOld, $ExtOld = '', $Sep = '') {     # return ProperCased FileName or FolderName with or without UPPERCased extension
# Side effects: $NamNew and $ExtNew are assigned components of return value

  $NamOldTmp = preg_replace("/[^a-zA-Z]/", "", $NamOld);      # prepare for Caseness check by removing all non-letters
  $CaseFixNeeded =                                            # Case needs fixing BaseName if input is either (is accurate for letters only)
    ctype_upper($NamOldTmp)                                   # All UPPERCase
    ||                                                        # or
    ctype_lower($NamOldTmp);                                  # All lowerCase

  if ($CaseFixNeeded) {                                       # i_f Case does     need fixing
    $NamNew = mb_convert_case($NamOld, MB_CASE_TITLE);        # fix Case
  } else {                                                    #    Case does not need fixing
    $NamNew = $NamOld;                                        # use existing Case
  }

  if ($ExtOld != '') {                                        # we have an Extension
    if (! ExtIsException($ExtOld)) {                          # this Extension is not excluded from UPPERcasing
      $ExtNew = mb_convert_case($ExtOld, MB_CASE_UPPER);      # UPPERCase it
    } else {                                                  # this Extension is     excluded from UPPERcasing
      $ExtNew = mb_convert_case($ExtOld, MB_CASE_LOWER);      # lowercase it
    }
    $NamExtNew = $NamNew . '.' . $ExtNew;                     # append it to BaseName
  } else {                                                    # we have no Extension
    $NamExtNew = $NamNew                ;                     # proceed with just BaseName
  }

  $NamExtNew = str_replace('_', ' ', $NamExtNew);             # convert underscores to spaces
  $NamExtNew = str_replace('+', ' ', $NamExtNew);             # convert plus        to spaces

  $NamExtNew = HyphensToSpaces($NamExtNew);                   # conditionally convert hyphens to spaces

  $NamExtNew = DateTimeStampFormatted($NamExtNew);            # format DateTime stamps

  return $NamExtNew;
} // NamExtNewGet
function NamOrNamExtFromPath($Path) {                         # return FileName or FileName.EXT from a PathName. Varies on whether Extension exists.
  $Arr = pathinfo($Path);                                     # parse source Path\FileName[.Ext]
  $Nam = $Arr['filename'];                                    # get FileName portion

  if (array_key_exists('extension', $Arr)) {                  # there is an Extension
    $Ext = '.' .  $Arr['extension'];                          # get Extension portion
  } else {                                                    # there is no Extension
    $Ext = ''                     ;                           # get new name without Extension
  }

  $Rslt = $Nam . $Ext;
  return $Rslt;
} // NamOrNamExtFromPath
function WorkOn($NamExtPrm) {
  $NamExtOld = realpath($NamExtPrm);                          # get actual filename if it exists (includes full path). If not found then returns ''
  Dbg('$NamExtOld', $NamExtOld);
  if ($NamExtOld == '') {                                     # no matching name found in filesystem
    echo '"' . $NamExtPrm . '" : ';                           # acknowledge what user asked for
    Abort('Not Found.', 2);                                   # complain because Name not found
  }

  $NamExtOld = NamOrNamExtFromPath($NamExtOld);               # parse FileName[.Ext] from source D:\Path\FileName[.Ext]
  echo '"' . $NamExtOld . '" ';                               # acknowledge what existing Name we're starting with

  $Pos = strrpos($NamExtOld, '.');                            # find last separator in NamExtOld. Returns FALSE if not found. Use === operator for testing return value.
  if (! $Pos === False) {                                     # there is an Extension
    $NamOld = substr($NamExtOld, 0       , $Pos);             # get everything before Separator
    $ExtOld = substr($NamExtOld, $Pos + 1      );             # get everything after  Separator
    $NamExtNew = NamExtNewGet($NamOld, $ExtOld, '.');         # get new name with    Extension
  } else {                                                    # there is no          Extension
    $NamExtNew = NamExtNewGet($NamExtOld           );         # get new name without Extension
  }

 #while (strrpos($NamExtNew, ',');                            # find last separator in NamExtOld. Returns FALSE if not found. Use === operator for testing return value.
  $NamExtNew = str_replace(",", ".", $NamExtNew);             # convert Commas to Periods

 #Dbg('$NamExtOld', $NamExtOld);
  if ($NamExtOld != $NamExtNew) {                             # existing Name should change
    echo 'to "'   . $NamExtNew . '"';                         # output what we're aiming at

    if (rename ($NamExtPrm, $NamExtNew)) {                    # i_f rename worked
      Msg(" : Success.");                                     # output action result
    } else {                                                  #  rename failed
      echo(" : Failure. ");                                   # output action result
      $stderr = fopen('php://stderr', 'w');                   # send Error
      fwrite($stderr, "ProperCase: " . $php_errormsg . "\n"); #   message to
      fclose($stderr);                                        #   StdErr
      Msg('');                                                # quit
    }
  } else {                                                    # existing Name is already good
    Msg(": No Change from " . $NamExtOld);                    # output action
  }
} // WorkOn

//*****************************************//
// * * * *   MAIN   * * * *                //
//*****************************************//

$Int  = ini_get('error_reporting');
$Int &= ~E_WARNING;                                           # turn off just Warnings
$Int  = ini_set('error_reporting', $Int);

MsgNoCrLf("ProperCase");                                      # banner
$NumPrm = $argc;                                              # get number of names to work on
for ($PrmNum = 1; $PrmNum < $NumPrm; $PrmNum++) {             # scan names to work on
  WorkOn($argv[$PrmNum]);                                     # work on each name
}

?>
