# Microsoft Developer Studio Project File - Name="gnokii" - Package Owner=<4>
# Microsoft Developer Studio Generated Build File, Format Version 6.00
# ** DO NOT EDIT **

# TARGTYPE "Win32 (x86) Console Application" 0x0103

CFG=gnokii - Win32 Debug
!MESSAGE This is not a valid makefile. To build this project using NMAKE,
!MESSAGE use the Export Makefile command and run
!MESSAGE 
!MESSAGE NMAKE /f "gnokii.mak".
!MESSAGE 
!MESSAGE You can specify a configuration when running NMAKE
!MESSAGE by defining the macro CFG on the command line. For example:
!MESSAGE 
!MESSAGE NMAKE /f "gnokii.mak" CFG="gnokii - Win32 Debug"
!MESSAGE 
!MESSAGE Possible choices for configuration are:
!MESSAGE 
!MESSAGE "gnokii - Win32 Release" (based on "Win32 (x86) Console Application")
!MESSAGE "gnokii - Win32 Debug" (based on "Win32 (x86) Console Application")
!MESSAGE 

# Begin Project
# PROP AllowPerConfigDependencies 0
# PROP Scc_ProjName ""
# PROP Scc_LocalPath ""
CPP=cl.exe
RSC=rc.exe

!IF  "$(CFG)" == "gnokii - Win32 Release"

# PROP BASE Use_MFC 0
# PROP BASE Use_Debug_Libraries 0
# PROP BASE Output_Dir "Release"
# PROP BASE Intermediate_Dir "Release"
# PROP BASE Target_Dir ""
# PROP Use_MFC 0
# PROP Use_Debug_Libraries 0
# PROP Output_Dir "Release"
# PROP Intermediate_Dir "Release"
# PROP Ignore_Export_Lib 0
# PROP Target_Dir ""
# ADD BASE CPP /nologo /MD /W3 /GX /O2 /D "WIN32" /D "NDEBUG" /D "_CONSOLE" /D "_MBCS" /YX /FD /c
# ADD CPP /nologo /MD /W3 /GX /O2 /I "." /I "../../include" /I "../../getopt" /D "WIN32" /D "NDEBUG" /D "_CONSOLE" /D "_MBCS" /D "GNOKIIDLL_IMPORTS" /YX /FD /c
# ADD BASE RSC /l 0x407 /d "NDEBUG"
# ADD RSC /l 0x407 /d "NDEBUG"
BSC32=bscmake.exe
# ADD BASE BSC32 /nologo
# ADD BSC32 /nologo
LINK32=link.exe
# ADD BASE LINK32 kernel32.lib user32.lib gdi32.lib winspool.lib comdlg32.lib advapi32.lib shell32.lib ole32.lib oleaut32.lib uuid.lib odbc32.lib odbccp32.lib kernel32.lib user32.lib gdi32.lib winspool.lib comdlg32.lib advapi32.lib shell32.lib ole32.lib oleaut32.lib uuid.lib odbc32.lib odbccp32.lib /nologo /subsystem:console /machine:I386
# ADD LINK32 /nologo /subsystem:console /machine:I386
# SUBTRACT LINK32 /verbose /debug /nodefaultlib

!ELSEIF  "$(CFG)" == "gnokii - Win32 Debug"

# PROP BASE Use_MFC 0
# PROP BASE Use_Debug_Libraries 1
# PROP BASE Output_Dir "Debug"
# PROP BASE Intermediate_Dir "Debug"
# PROP BASE Target_Dir ""
# PROP Use_MFC 0
# PROP Use_Debug_Libraries 1
# PROP Output_Dir "Debug"
# PROP Intermediate_Dir "Debug"
# PROP Ignore_Export_Lib 0
# PROP Target_Dir ""
# ADD BASE CPP /nologo /W3 /Gm /GX /ZI /Od /D "WIN32" /D "_DEBUG" /D "_CONSOLE" /D "_MBCS" /YX /FD /GZ /c
# ADD CPP /nologo /MDd /W3 /GX /Zi /Od /I "." /I "../../include" /I "../../getopt" /D "WIN32" /D "_DEBUG" /D "_CONSOLE" /D "_MBCS" /D "GNOKIIDLL_IMPORTS" /FR /FD /GZ /c
# SUBTRACT CPP /YX
# ADD BASE RSC /l 0x407 /d "_DEBUG"
# ADD RSC /l 0x407 /d "_DEBUG"
BSC32=bscmake.exe
# ADD BASE BSC32 /nologo
# ADD BSC32 /nologo
LINK32=link.exe
# ADD BASE LINK32 kernel32.lib user32.lib gdi32.lib winspool.lib comdlg32.lib advapi32.lib shell32.lib ole32.lib oleaut32.lib uuid.lib odbc32.lib odbccp32.lib kernel32.lib user32.lib gdi32.lib winspool.lib comdlg32.lib advapi32.lib shell32.lib ole32.lib oleaut32.lib uuid.lib odbc32.lib odbccp32.lib /nologo /subsystem:console /debug /machine:I386 /pdbtype:sept
# ADD LINK32 Debug\gnokiid.lib msvcrtd.lib /nologo /subsystem:console /incremental:no /debug /machine:I386
# SUBTRACT LINK32 /verbose

!ENDIF 

# Begin Target

# Name "gnokii - Win32 Release"
# Name "gnokii - Win32 Debug"
# Begin Group "Source Files"

# PROP Default_Filter "cpp;c;cxx;rc;def;r;odl;idl;hpj;bat"
# Begin Source File

SOURCE=..\..\getopt\getopt.c
# End Source File
# Begin Source File

SOURCE=..\..\getopt\getopt1.c
# End Source File
# Begin Source File

SOURCE="..\..\gnokii\gnokii-app.h"
# End Source File
# Begin Source File

SOURCE="..\..\gnokii\gnokii-calendar.c"
# End Source File
# Begin Source File

SOURCE="..\..\gnokii\gnokii-dial.c"
# End Source File
# Begin Source File

SOURCE="..\..\gnokii\gnokii-file.c"
# End Source File
# Begin Source File

SOURCE="..\..\gnokii\gnokii-logo.c"
# End Source File
# Begin Source File

SOURCE="..\..\gnokii\gnokii-mms.c"
# End Source File
# Begin Source File

SOURCE="..\..\gnokii\gnokii-monitor.c"
# End Source File
# Begin Source File

SOURCE="..\..\gnokii\gnokii-other.c"
# End Source File
# Begin Source File

SOURCE="..\..\gnokii\gnokii-phonebook.c"
# End Source File
# Begin Source File

SOURCE="..\..\gnokii\gnokii-profile.c"
# End Source File
# Begin Source File

SOURCE="..\..\gnokii\gnokii-ringtone.c"
# End Source File
# Begin Source File

SOURCE="..\..\gnokii\gnokii-security.c"
# End Source File
# Begin Source File

SOURCE="..\..\gnokii\gnokii-settings.c"
# End Source File
# Begin Source File

SOURCE="..\..\gnokii\gnokii-sms.c"
# End Source File
# Begin Source File

SOURCE="..\..\gnokii\gnokii-todo.c"
# End Source File
# Begin Source File

SOURCE="..\..\gnokii\gnokii-utils.c"
# End Source File
# Begin Source File

SOURCE="..\..\gnokii\gnokii-wap.c"
# End Source File
# Begin Source File

SOURCE=..\..\gnokii\gnokii.c
# ADD CPP /I "../../getopt"
# End Source File
# End Group
# End Target
# End Project
