<?php
$YOU_HAVE_JOINED = "Du är nu medlem. Skicka HJÄLP för hjälp. För att lämna listan, skicka PAUS. Skicka till: 0727301059. Skickar, men tar ej emot gör: 0727301124 och 0727301048.";
$YOU_HAVE_QUIT = "Du är nu borttagen ur smslistan.";
$INCORRECT_SYNTAX = "Felaktig syntax. Vänligen kolla att du skrev rätt.";
$NO_SUCH_CASE = "Felaktigt id. Skrev du rätt?";
$USER_HAS_BEEN_BANNED = "Användaren har bannats.";
$MEMBERSHIP_OVER = "Din medlemstid i Plankas SMS-system är nu slut. Skicka ett sms till 72550 med texten 'plankagbg varnamig' för att förlänga.";
$WARNING_NO_MONEY = "Varning! En mobil börjar få slut på pengar! Bara 100 sms kvar!";
$MEMBER_TIME_POSTGRES_INTERVAL_STRING = "1 year";
$TWITTER_USERNAME = 'testingblaha';
$TWITTER_PASSWORD = 'antonanton';
$DB_HOST = 'localhost';
$DB_USER = 'postgres';
$DB_PASS = 'biljetten';
$DB_NAME = 'planka';

$DEBUG_OUTPUT = true;
$USE_MESSAGE_CONFIRMATION = true;




$MESSAGE_CONFIRMATION = 'Tack för ditt tips! Vi ser nu över det och beslutar om det ska skickas ut.';

//Admin errors
$NO_SUCH_COMMAND = "Kommandot finns inte: ";
$NO_COMMAND_GIVEN = "Fick inget kommando.";
$INCORRECT_PARAMETER_COUNT = ": felaktigt antal parametrar.";
 
// Perform errors
$NO_SUCH_CASE = "Finns inget ärende med detta id";
$NO_SUCH_CASE_TYPE_NAME = "Finns ingen sådan meddelandetyp.";
$CASE_TYPE_DOES_NOT_ALLOW_THIS_ACTION = "Meddelandetypen tillåter inte denna handling.";
$MESSAGE_TO_LONG_TO_TWITTER = "Meddelandet för långt för att twittras.";

  
//User errors
$UNSUBSCRIBE_TOO_MANY_ARGUMENTS = "Smssystemet: För många ord. Prenumerationspaus tar inga parametrar.";
$RESUBSCRIBE_TOO_MANY_ARGUMENTS = "Smssystemet: För många ord. Prenumerationsstart tar inga parametrar.";
$HELP_TOO_MANY_ARGUMENTS = "Smssystemet: För många ord. Hjälp tar inga parametrar.";
$JOIN_TOO_MANY_ARGUMENTS = "Smssystemet: För många ord. Join tar inga parametrar.";
$EMPTY_SMS_FROM_MEMBER = "Smssystemet: Det där SMS:et såg tomt ut för oss. Har du en konstig mobil?";
$NOT_AN_ID = "Det där ser inte ut som ett meddelande-ID.";

$COULD_NOT_PARSE = "Fel vid inläsning: ";
$COULD_NOT_PERFORM = "Fel vid körning: ";


$MAX_SEND_OUT_LENGTH = 142;
$MESSAGE_TO_LONG = "Meddelandet var för långt.";


$YOU_ARE_REACTIVATED = "Ditt medlemsskap är återaktiverat.";

//Will this become fucked up?
$MEMBERSHIP_CONFIRMATION_MESSAGE = 'Din betalning har nu tagits emot av Kontrollantkoll. Du ar nu medlem under hela test-perioden. /Kontrollantkoll';

$MEMBERSHIP_LENGTH_POSTGRES_INTERVAL = '3 months';
$CHECK_FOR_MESSAGE_INTERVAL_IN_SECONDS = 10;

$PREVIOUS_CASE_ID = '..';
$COMMAND_SPLIT_TOKEN = '|';

$USER_HELP_TEXT = 'Meddelanden kollas och skickas ut. Kommandon: paus - pausa medlemskap. start - starta pausat medlemskap.';

$ADMIN_COMMANDS['SEND_DEFAULT_METHOD'] = 's';
$ADMIN_COMMANDS['SEND_OUT_CASE'] = 'sendonly';
$ADMIN_COMMANDS['SEND_OUT_TWITTER_CASE'] = 'sendtwitter';
$ADMIN_COMMANDS['REJECT_CASE'] = 'r';
$ADMIN_COMMANDS['CHANGE_CASE'] = 'ch';
$ADMIN_COMMANDS['TWITTER_CASE'] = 'twitter';
$ADMIN_COMMANDS['STATISTICS'] = 'stat';
$ADMIN_COMMANDS['BAN_NUMBER'] = 'ban';
$ADMIN_COMMANDS['SET_CASE_TYPE'] = 'set';
$ADMIN_COMMANDS['CREATE_CASE'] = 'cr';
$ADMIN_COMMANDS['SEND_PRIVATE'] = 'priv';
  
$USER_COMMANDS['RESUBSCRIBE'] = 'start';
$USER_COMMANDS['UNSUBSCRIBE'] = 'paus';
$USER_COMMANDS['JOIN'] = 'joincommandissecret';
$USER_COMMANDS['HELP'] = 'hjälp';


?>
