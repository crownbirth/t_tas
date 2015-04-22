<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>
</head>

<body>
<table width="1015" border="1" align="center">
  <tr>
    <td width="43" align="center">S/N</td>
    <td width="117" align="center">&nbsp;</td>
    <td colspan="2" align="center"><table width="99%" border="0">
      <tr>
        <td align="center">Current Semester course</td>
        </tr>
    </table></td>
    <td width="177" align="center">Previous Semester Course </td>
    <td colspan="3" align="center">Grade Points</td>
    <td width="179" align="center">Outstanding</td>
  </tr>
  <tr>
    <td rowspan="3">&nbsp;</td>
    <td>Course Code </td>
    <td width="98"  align="center">cc</td>
     <td width="101"  align="center">cc</td>
    <td rowspan="3" align="center">Individual Reference Courses</td>
    <td width="80" rowspan="3" align="center" valign="top">Previous</td>
    <td width="71" rowspan="3" align="center" valign="top">Current</td>
    <td width="91" rowspan="3" align="center" valign="top">Cummulative</td>
    <td rowspan="3">&nbsp;</td>
  </tr>
  <tr>
    <td>Course Status</td>
    <td colspan="2" align="center">cs</td>
  </tr>
  <tr>
    <td>Course Unit</td>
    <td colspan="2" align="center">cu</td>
  </tr>
          <tr align="center">
            <td height="33" rowspan="2">1</td>
            <td rowspan="2">20090204009</td>
            <td colspan="2" align="center">44</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
          </tr>
  <tr>
    <td colspan="2" align="center">E1</td>
    <td align="center">&nbsp;</td>
    <td align="center">&nbsp;</td>
    <td align="center">&nbsp;</td>
    <td align="center">&nbsp;</td>
    <td align="center">&nbsp;</td>
  </tr>
</table> 

<p>&nbsp;</p>
<p>
  <?php 
echo "Posted semster = ".$_POST['semester2']
?>
</p>
<p>
<?php 
echo "Posted college = ".$_POST['col']
?></p>
<p>
<?php 
echo"Posted program = ". $_POST['prog']
?>
</p>
<p>
<?php 
echo"Posted level  = ". $_POST['level']
?>
</p>
<p>
<?php 
echo "Posted session = ".$_POST['session']
?>
</p>
</body>
</html>