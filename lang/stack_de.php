<?php
// This file is part of Stack - http://stack.maths.ed.ac.uk//
//
// Stack is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Stack is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Stack.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Language strings for the STACK question type.
 *
 * @package    qtype_stack
 * @copyright  2012 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'STACK';
$string['pluginname_help'] = 'STACK ist ein Assessmentsystem für Mathematik.';
$string['pluginnameadding'] = 'STACK-Frage hinzufügen';
$string['pluginnameediting'] = 'STACK-Frage bearbeiten';
$string['pluginnamesummary'] = 'STACK ermöglicht es mathematische Fragestellungen in Moodle-Tests zu verwenden. Es bedient sich dabei eines Computeralgebrasystems um mathematische Eigenschaften der eingegebenen Antworten zu ermitteln und diese dann zu bewerten.';

$string['privacy:metadata'] = 'Das STACK-Fragetyp-Plugin speichert keine persönlichen Daten.';
$string['cachedef_parsercache'] = 'Von STACK geparste Maxima-Ausdrücke';

$string['mbstringrequired'] = 'Die Installation der MBSTRING-Bibliothek ist für STACK erforderlich.';
$string['yamlrecommended'] = 'Die Installation der YAML-Bibliothek wird für STACK empfohlen.';

// Allgemeine Strings.
$string['errors'] = 'Fehler';
$string['debuginfo'] = 'Debug-Informationen';
$string['exceptionmessage'] = '{$a}';
$string['runtimeerror'] = 'Diese Frage hat einen unerwarteten internen Fehler verursacht. Bitte suchen Sie Rat, z.B. bei einem Lehrer.';
$string['runtimefielderr'] = 'Das Feld ""{$a->field}"" hat den folgenden Fehler verursacht: {$a->err}';
$string['version'] = 'Version';

// Berechtigungsnamen.
$string['stack:usediagnostictools'] = 'Die STACK-Werkzeuge verwenden';

// Versionen von STACK.
$string['stackversionedited'] = 'Diese Frage wurde mit der STACK-Version {$a} erstellt.';
$string['stackversionnow'] = 'Die aktuelle Version von STACK ist {$a}.';
$string['stackversionnone'] = 'Diese Frage wurde seit der Einführung der Variantennummerierung in STACK 7.0 nicht bearbeitet. Bitte überprüfen Sie Ihre Frage sorgfältig.';
$string['stackversionerror'] = 'Diese Frage verwendet {$a->pat} im {$a->qfield}, was sich in der STACK-Version {$a->ver} geändert hat und nicht mehr unterstützt wird.';
$string['stackversionerroralt'] = 'Eine Alternative ist {$a}.';
$string['stackversionmulerror'] = 'Diese Frage hat eine Eingabe, die die "mul"-Option verwendet, die nach STACK-Version 7.0 nicht unterstützt wird. Bitte bearbeiten Sie diese Frage.';
$string['stackversionregexp'] = 'Der RegExp-Antworttest wird nach STACK-Version 7.5 nicht unterstützt. Bitte verwenden Sie stattdessen das neue SRegExp.';
$string['stackfileuseerror'] = 'Mit dem {$a} sind intern eine oder mehrere Dateien (z.B. Bilder) verknüpft, aber keine scheint im aktuellen Text selbst verwendet zu werden.';

// Strings, die im Bearbeitungsformular verwendet werden.
$string['generalerrors'] = 'Es gibt Fehler in Ihrer Frage. Bitte prüfen Sie sorgfältig unten.';
$string['addanothernode'] = 'Einen weiteren Knoten hinzufügen';
$string['allnodefeedbackmustusethesameformat'] = 'Das gesamte Feedback für alle Knoten in einem PRT muss das gleiche Textformat verwenden.';
$string['answernote'] = 'Antwortnotiz';
$string['answernote_err'] = 'Antwortnotizen dürfen das Zeichen | nicht enthalten. Dieses Zeichen wird von STACK eingefügt und später verwendet, um Antwortnotizen automatisch zu trennen.';
$string['answernote_err2'] = 'Antwortnotizen dürfen die Zeichen ; oder : nicht enthalten. Diese Zeichen werden verwendet, um Frageversuchs-Zusammenfassungszeichenfolgen in Offline-Berichtswerkzeugen zu trennen.';
$string['answernote_help'] = 'Dies ist ein Tag, der für Berichtszwecke wichtig ist. Es soll den einzigartigen Weg durch den Baum und das Ergebnis jedes Antworttests aufzeichnen. Dies wird automatisch generiert, kann aber in etwas Sinnvolles geändert werden.';
$string['answernote_link'] = '%%WWWROOT%%/question/type/stack/doc/doc.php/Authoring/Potential_response_trees.md#Answer_note';
$string['answernotedefaultfalse'] = '{$a->prtname}-{$a->nodename}-F';
$string['answernotedefaulttrue'] = '{$a->prtname}-{$a->nodename}-T';
$string['answernoterequired'] = 'Antwortnotiz darf nicht leer sein.';
$string['answernoteunique'] = 'Doppelte Antwortnotizen in diesem potenziellen Antwortbaum erkannt.';
$string['assumepositive'] = 'Positiv annehmen';
$string['assumepositive_help'] = 'Diese Option legt den Wert der Maxima-Variablen assume_pos fest.';
$string['assumepositive_link'] = '%%WWWROOT%%/question/type/stack/doc/doc.php/Authoring/Options.md#Assume_Positive';
$string['assumereal'] = 'Real annehmen';
$string['assumereal_help'] = 'Diese Option legt die Variable assume_real fest.';
$string['assumereal_link'] = '%%WWWROOT%%/question/type/stack/doc/doc.php/Authoring/Options.md#Assume_Real';
$string['autosimplify'] = 'Auto-Vereinfachung';
$string['autosimplify_help'] = 'Legt die Variable "simp" innerhalb von Maxima für diese Frage fest. Z.B. Fragevariablen, Fragetext usw. Der Wert, der in jedem potenziellen Antwortbaum festgelegt wird, hebt diesen für alle danach definierten Ausdrücke innerhalb des Baumes auf.';
$string['autosimplify_link'] = '%%WWWROOT%%/question/type/stack/doc/doc.php/CAS/Maxima.md#Vereinfachung';
$string['autosimplifyprt'] = 'Auto-Vereinfachung';
$string['autosimplifyprt_help'] = 'Legt die Variable "simp" innerhalb von Maxima für die Feedback-Variablen fest, die in diesem potenziellen Antwortbaum definiert sind. Ob Ausdrücke in PRT-Notizen vor der Verwendung vereinfacht werden, hängt vom Antworttest ab. Zum Beispiel werden Argumente für AlgEquiv vereinfacht, während die für EqualComAss nicht vereinfacht werden.';
$string['autosimplifyprt_link'] = '%%WWWROOT%%/question/type/stack/doc/doc.php/CAS/Maxima.md#Vereinfachung';
$string['boxsize'] = 'Größe des Eingabefelds';
$string['boxsize_help'] = 'Breite des HTML-Formularfelds.';
$string['boxsize_link'] = '%%WWWROOT%%/question/type/stack/doc/doc.php/Authoring/Inputs.md#Box_Size';
$string['bulktestindexintro_desc'] = 'Das <a href="{$a->link}">Massentest-Skript</a> ermöglicht es Ihnen, alle STACK-Fragentests in einem bestimmten Kontext einfach durchzuführen. Dies testet nicht nur die Fragen. Es ist auch eine gute Möglichkeit, den CAS-Cache wieder aufzufüllen, nachdem er gelöscht wurde.';
$string['dependenciesintro_desc'] = 'Der <a href="{$a->link}">Abhängigkeitschecker</a> findet Fragen mit Abhängigkeiten wie JSXGraph oder Einbindung von externem Maxima-Code.';
$string['checkanswertype'] = 'Den Typ der Antwort überprüfen';
$string['checkanswertype_help'] = 'Wenn ja, werden Antworten, die von einem anderen "Typ" sind (z.B. Ausdruck, Gleichung, Matrix, Liste, Menge), als ungültig abgelehnt.';
$string['checkanswertype_link'] = '%%WWWROOT%%/question/type/stack/doc/doc.php/Authoring/Inputs.md#Check_Type';
$string['complexno'] = 'Bedeutung und Darstellung von sqrt(-1)';
$string['complexno_help'] = 'Steuert die Bedeutung und Darstellung des Symbols i und sqrt(-1)';
$string['complexno_link'] = '%%WWWROOT%%/question/type/stack/doc/doc.php/Authoring/Options.md#sqrt_minus_one.';
$string['defaultmarkzeroifnoprts'] = 'Die Standardbewertung muss 0 sein, wenn diese Frage keine PRTs hat.';
$string['defaultprtcorrectfeedback'] = 'Richtige Antwort, gut gemacht.';
$string['defaultprtincorrectfeedback'] = 'Falsche Antwort.';
$string['defaultprtpartiallycorrectfeedback'] = 'Ihre Antwort ist teilweise richtig.';
$string['symbolicprtcorrectfeedback'] = '<span style="font-size: 1.5em; color:green;"><i class="fa fa-check"></i></span>';
$string['symbolicprtincorrectfeedback'] = '<span style="font-size: 1.5em; color:red;"><i class="fa fa-times"></i></span>';
$string['symbolicprtpartiallycorrectfeedback'] = '<span style="font-size: 1.5em; color:orange;"><i class="fa fa-adjust"></i></span>';
$string['branchfeedback'] = 'Feedback zum Knotenast';
$string['branchfeedback_help'] = 'Dies ist CASText, der von allen Fragevariablen, Eingabeelementen oder Feedback-Variablen abhängen kann. Dieser wird ausgewertet und dem Studenten angezeigt, wenn er diesen Ast passiert.';
$string['inputtest'] = 'Eingabetest';
$string['inversetrig'] = 'Inverse trigonometrische Funktionen';
$string['inversetrig_help'] = 'Steuert, wie inverse trigonometrische Funktionen in CAS-Ausgaben dargestellt werden.';
$string['inversetrig_link'] = '%%WWWROOT%%/question/type/stack/doc/doc.php/Authoring/Options.md#inverse_trig';
$string['logicsymbol'] = 'Logiksymbole';
$string['logicsymbol_help'] = 'Steuert, wie logische Symbole in CAS-Ausgaben dargestellt werden.';
$string['logicsymbol_link'] = '%%WWWROOT%%/question/type/stack/doc/doc.php/Authoring/Options.md#logicsymbol';
$string['logicsymbollang'] = 'Sprache';
$string['logicsymbolsymbol'] = 'Symbolisch';
$string['matrixparens'] = 'Standardform von Matrixklammern';
$string['matrixparens_help'] = 'Steuert die Standardform von Matrixklammern, wenn sie in CAS-Ausgaben angezeigt werden.';
$string['matrixparens_link'] = '%%WWWROOT%%/question/type/stack/doc/doc.php/CAS/Matrix.md#matrixparens';
$string['falsebranch'] = 'Falscher Ast';
$string['falsebranch_help'] = 'Diese Felder steuern, was passiert, wenn der Antworttest nicht besteht.
### Mod und Punktzahl
Wie die Punktzahl angepasst wird. = bedeutet, die Punktzahl auf einen bestimmten Wert zu setzen, +/- bedeutet, die angegebene Punktzahl zum aktuellen Gesamt hinzuzufügen oder abzuziehen.

### Strafe
In adaptiven oder interaktiven Modi wird so viel Strafe angesammelt.

### Weiter
Ob zu einem anderen Knoten gegangen werden soll, und wenn ja, zu welchem, oder ob gestoppt wird.

### Antwortnotiz
Dies ist ein Tag, der für Berichtszwecke wichtig ist. Es soll den einzigartigen Weg durch den Baum und das Ergebnis jedes Antworttests aufzeichnen. Dies wird automatisch generiert, kann aber in etwas Sinnvolles geändert werden.
';
$string['feedbackfromprtx'] = '[ Feedback von {$a}. ]';
$string['feedbackvariables'] = 'Feedback-Variablen';
$string['feedbackvariables_help'] = 'Die Feedback-Variablen ermöglichen es Ihnen, alle Eingaben zusammen mit den Fragevariablen vor dem Durchlaufen des Baums zu manipulieren. Variablen, die hier definiert sind, können überall in diesem Baum verwendet werden.';
$string['feedbackvariables_link'] = '%%WWWROOT%%/question/type/stack/doc/doc.php/Authoring/Variables.md#Feedback_variables';
$string['fieldshouldnotcontainplaceholder'] = '{$a->field} sollte keine [[{$a->type}:...]] Platzhalter enthalten.';
$string['firstnodemusthavelowestnumber'] = 'Der erste Knoten muss die niedrigste Nummer haben.';
$string['fixdollars'] = 'Dollarzeichen korrigieren';
$string['fixdollarslabel'] = 'Ersetzen Sie <code>$...$</code> mit <code></code>, <code>.</code> mit <code></code> und <code>@...@</code> mit <code>{@...@}</code> beim Speichern.';
$string['fixdollars_help'] = 'Diese Option ist nützlich, wenn Sie TeX mit <code>$...$</code> und <code></code> Begrenzern kopieren und einfügen (oder tippen). Diese Begrenzer werden während des Speicherprozesses durch die empfohlenen Begrenzer ersetzt.';
$string['forbiddendoubledollars'] = 'Bitte verwenden Sie die Begrenzer <code></code> für Inline-Mathematik und <code></code> für angezeigte Mathematik. <code>$...$</code> und <code></code> sind nicht erlaubt. Am Ende des Formulars gibt es eine Option, um dies automatisch zu korrigieren.';
$string['forbidfloat'] = 'Gleitkommazahlen verbieten';
$string['forbidfloat_help'] = 'Wenn auf Ja gesetzt, wird jede Antwort des Schülers, die eine Gleitkommazahl enthält, als ungültig abgelehnt.';
$string['forbidfloat_link'] = '%%WWWROOT%%/question/type/stack/doc/doc.php/Authoring/Inputs.md#Forbid_Floats';
$string['forbidwords'] = 'Verbotene Wörter ';
$string['forbidwords_help'] = 'Dies ist eine durch Kommas getrennte Liste von Textzeichenfolgen, die in der Antwort eines Schülers verboten sind.';
$string['forbidwords_link'] = '%%WWWROOT%%/question/type/stack/doc/doc.php/Authoring/Inputs.md#Forbidden_Words';
$string['allowwords'] = 'Erlaubte Wörter ';
$string['allowwords_help'] = 'Standardmäßig sind willkürliche Funktions- oder Variablennamen mit mehr als zwei Zeichen Länge nicht gestattet. Dies ist eine durch Kommas getrennte Liste von Funktions- oder Variablennamen, die in einer Antwort eines Schülers erlaubt sind.';
$string['allowwords_link'] = '%%WWWROOT%%/question/type/stack/doc/doc.php/Authoring/Inputs.md#Allow_Words';
$string['generalfeedback'] = 'Allgemeines Feedback';
$string['generalfeedback_help'] = 'Allgemeines Feedback ist CASText. Allgemeines Feedback, auch bekannt als "ausgearbeitete Lösung", wird dem Schüler gezeigt, nachdem er die Frage versucht hat. Im Gegensatz zum Feedback, das davon abhängt, welche Antwort der Schüler gegeben hat, wird allen Schülern der gleiche allgemeine Feedback-Text gezeigt. Es kann von den in der Variante der Frage verwendeten Fragevariablen abhängen.';
$string['generalfeedback_link'] = '%%WWWROOT%%/question/type/stack/doc/doc.php/Authoring/CASText.md#general_feedback';
$string['showvalidation'] = 'Die Validierung anzeigen';
$string['showvalidation_help'] = 'Zeigt jedes Validierungsfeedback von dieser Eingabe an, einschließlich des Zurückspiegelns ihres Ausdrucks in traditioneller zweidimensionaler Notation. Syntaxfehler werden immer zurückgemeldet.';
$string['showvalidation_link'] = '%%WWWROOT%%/question/type/stack/doc/doc.php/Authoring/Inputs.md#Show_validation';
$string['showvalidationno'] = 'Nein';
$string['showvalidationyes'] = 'Ja, mit Variablenliste';
$string['showvalidationyesnovars'] = 'Ja, ohne Variablenliste';
$string['showvalidationcompact'] = 'Ja, kompakt';
$string['inputinvalidparamater'] = 'Ungültiger Parameter';
$string['mustverifyshowvalidation'] = 'Sie können nicht wählen, eine zweistufige Validierung zu verlangen, aber nicht die Ergebnisse der Validierung nach dem ersten Schritt dem Schüler zu zeigen. Das setzt den Schüler in eine unmögliche Lage.';
$string['htmlfragment'] = 'Es scheint, dass Sie einige HTML-Elemente in Ihrem Ausdruck haben.';
$string['illegalcaschars'] = 'Die Zeichen @, $ und \ sind in CAS-Eingaben nicht erlaubt.';
$string['inputextraoptions'] = 'Zusätzliche Optionen';
$string['inputextraoptions_help'] = 'Einige Eingabetypen erfordern zusätzliche Optionen, um zu funktionieren. Sie können sie hier eingeben. Dieser Wert ist ein CAS-Ausdruck.';
$string['inputextraoptions_link'] = '%%WWWROOT%%/question/type/stack/doc/doc.php/Authoring/Inputs.md#Extra_options';
$string['inputoptionunknown'] = 'Dieser Eingabetyp unterstützt die Option {$a} nicht.';
$string['inputheading'] = 'Eingabe: {$a}';
$string['inputnamelength'] = 'Eingabenamen dürfen nicht länger als 18 Zeichen sein. {$a} ist zu lang.';
$string['inputnameform'] = 'Eingabenamen müssen nur aus Buchstaben bestehen, denen (optional) Zahlen folgen können. {$a} ist ungültig.';
$string['inputremovedconfirmbelow'] = 'Eingabe {$a} wurde entfernt. Bitte bestätigen Sie dies unten.';
$string['inputremovedconfirm'] = 'Ich bestätige, dass ich diese Eingabe aus dieser Frage entfernen möchte.';
$string['inputlanguageproblems'] = 'Es gibt Inkonsistenzen in den Eingabe- und Validierungstags zwischen den Sprachen.';
$string['inputs'] = 'Eingaben';
$string['inputtype'] = 'Eingabetyp';
$string['inputtype_help'] = 'Dies bestimmt den Typ des Eingabeelements, z.B. Formularfeld, Wahr/Falsch, Textbereich.';
$string['inputtype_link'] = '%%WWWROOT%%/question/type/stack/doc/doc.php/Authoring/Inputs.md';
$string['inputtypealgebraic'] = 'Algebraische Eingabe';
$string['inputtypeboolean'] = 'Wahr/Falsch';
$string['inputtypedropdown'] = 'Dropdown-Liste';
$string['inputtypecheckbox'] = 'Kontrollkästchen';
$string['inputtyperadio'] = 'Radio';
$string['inputtypesinglechar'] = 'Einzelner Buchstabe';
$string['inputtypetextarea'] = 'Textbereich';
$string['inputtypematrix'] = 'Matrix';
$string['inputtypevarmatrix'] = 'Matrix variabler Größe';
$string['inputtypenotes'] = 'Notizen';
$string['inputtypeunits'] = 'Einheiten';
$string['inputtypeequiv'] = 'Äquivalenzbegründung';
$string['inputtypestring'] = 'String';
$string['inputtypenumerical'] = 'Numerisch';
$string['inputtypegeogebra'] = 'GeoGebra';
$string['numericalinputmustnumber'] = 'Diese Eingabe erwartet eine Zahl.';
$string['numericalinputvarsforbidden'] = 'Diese Eingabe erwartet eine Zahl und darf daher keine Variablen enthalten.';
$string['numericalinputmustfloat'] = 'Diese Eingabe erwartet eine Gleitkommazahl.';
$string['numericalinputmustint'] = 'Diese Eingabe erwartet eine explizite ganze Zahl.';
$string['numericalinputmustrational'] = 'Diese Eingabe erwartet einen Bruch oder eine rationale Zahl.';
$string['numericalinputdp'] = 'Sie müssen genau {$a} Dezimalstellen angeben.';
$string['numericalinputsf'] = 'Sie müssen genau {$a} signifikante Stellen angeben.';
$string['numericalinputmindp'] = 'Sie müssen mindestens {$a} Dezimalstellen angeben.';
$string['numericalinputmaxdp'] = 'Sie dürfen höchstens {$a} Dezimalstellen angeben.';
$string['numericalinputminsf'] = 'Sie müssen mindestens {$a} signifikante Stellen angeben.';
$string['numericalinputmaxsf'] = 'Sie dürfen höchstens {$a} signifikante Stellen angeben.';
$string['numericalinputminmaxerr'] = 'Die erforderliche Mindestanzahl von Dezimalstellen übersteigt die maximal zulässige Anzahl von Stellen!';
$string['numericalinputminsfmaxdperr'] = 'Geben Sie keine Anforderungen für Dezimalstellen und signifikante Stellen in derselben Eingabe an.';
$string['numericalinputoptinterr'] = 'Der Wert der Option <code>{$a->opt}</code> sollte eine ganze Zahl sein, ist aber tatsächlich <code>{$a->val}</code>.';
$string['numericalinputoptboolerr'] = 'Der Wert der Option <code>{$a->opt}</code> sollte boolesch sein, ist aber tatsächlich <code>{$a->val}</code>.';
$string['inputvalidatorerr'] = 'Der Name einer Validierungsfunktion muss ein gültiger Maxima-Bezeichner in Form von Buchstaben a-zA-Z sein, optional gefolgt von Ziffern.';
$string['inputvalidatorerrcouldnot'] = 'Die optionale Validierungsfunktion hat interne Maxima-Fehler verursacht.';
$string['inputvalidatorerrors'] = 'Die optionale Validierungsfunktion gab die Fehler {$a->err} zurück.';
$string['inputopterr'] = 'Der Wert der Option <code>{$a->opt}</code> kann nicht als <code>{$a->val}</code> angegeben werden.';
$string['inputwillberemoved'] = 'Diese Eingabe wird im Fragetext nicht mehr erwähnt. Wenn Sie die Frage jetzt speichern, gehen die Daten zu dieser Eingabe verloren. Bitte bestätigen Sie, dass Sie dies tun möchten. Alternativ bearbeiten Sie den Fragetext, um die Platzhalter '."[[input:x]]".' und [[validation:{x}]] zurückzusetzen.';
$string['insertstars'] = 'Sterne einfügen';
$string['insertstars_help'] = 'Diese Option bietet verschiedene Möglichkeiten, Sterne einzufügen, wo Multiplikation impliziert ist. Bitte lesen Sie die detailliertere Dokumentation.';
$string['insertstars_link'] = '%%WWWROOT%%/question/type/stack/doc/doc.php/Authoring/Inputs.md#Insert_Stars';
$string['insertstarsno'] = 'Keine Sterne einfügen';
$string['insertstarsyes'] = 'Sterne nur bei implizierter Multiplikation einfügen';
$string['insertstarsassumesinglechar'] = 'Sterne einfügen unter der Annahme von einstelligen Variablennamen';
$string['insertspaces'] = 'Sterne nur für Leerzeichen einfügen';
$string['insertstarsspaces'] = 'Sterne für implizierte Multiplikation und für Leerzeichen einfügen';
$string['insertstarsspacessinglechar'] = 'Sterne einfügen unter der Annahme von einstelligen Variablen, impliziert und für Leerzeichen';
$string['decimals'] = 'Dezimaltrennzeichen';
$string['decimals_help'] = 'Wählen Sie das Symbol und die Optionen für das Dezimaltrennzeichen.';
$string['decimals_link'] = '%%WWWROOT%%/question/type/stack/doc/doc.php/Authoring/Options.md#decimals';
$string['multcross'] = 'Kreuz';
$string['multdot'] = 'Punkt';
$string['multonlynumbers'] = 'Nur Zahlen';
$string['multiplicationsign'] = 'Multiplikationszeichen';
$string['multiplicationsign_help'] = 'Steuert, wie Multiplikationszeichen angezeigt werden.';
$string['multiplicationsign_link'] = '%%WWWROOT%%/question/type/stack/doc/doc.php/Authoring/Options.md#multiplication';
$string['mustverify'] = 'Student muss verifizieren';
$string['mustverify_help'] = 'Gibt an, ob die Eingabe des Studenten ihm als erzwungener Zwei-Schritte-Prozess zurückgegeben wird, bevor diese Eingabe dem Bewertungsmechanismus zur Verfügung gestellt wird. Syntaxfehler werden immer zurückgemeldet.';
$string['mustverify_link'] = '%%WWWROOT%%/question/type/stack/doc/doc.php/Authoring/Inputs.md#Student_must_verify';
$string['namealreadyused'] = 'Sie haben diesen Namen bereits verwendet.';
$string['newnameforx'] = 'Neuer Name für \'{$a}\'';
$string['next'] = 'Nächster';
$string['nextcannotbeself'] = 'Ein Knoten kann sich nicht selbst als nächsten Knoten verlinken.';
$string['nodehelp'] = 'Knoten des Antwortbaums';
$string['nodehelp_help'] = '### Antworttest
Ein Antworttest wird verwendet, um zwei Ausdrücke zu vergleichen, um festzustellen, ob sie bestimmte mathematische Kriterien erfüllen.
SAns
Dies ist das erste Argument für die Antworttestfunktion. Bei asymmetrischen Tests wird dies als "Antwort des Studenten" betrachtet, obwohl es jeder gültige CAS-Ausdruck sein kann und von den Fragevariablen oder den Feedback-Variablen abhängen kann.
TAns
Dies ist das zweite Argument für die Antworttestfunktion. Bei asymmetrischen Tests wird dies als "Antwort des Lehrers" betrachtet, obwohl es jeder gültige CAS-Ausdruck sein kann und von den Fragevariablen oder den Feedback-Variablen abhängen kann.
Testoptionen
Dieses Feld ermöglicht es Antworttests, eine Option zu akzeptieren, z. B. eine Variable oder eine numerische Präzision.
Quiet
Wenn auf ja gesetzt, wird jedes Feedback, das automatisch von den Antworttests generiert wird, unterdrückt und nicht dem Studenten angezeigt. Die Feedbackfelder in den Verzweigungen werden von dieser Option nicht beeinflusst.
';
$string['nodeloopdetected'] = 'Dieser Link erzeugt eine Schleife in diesem Rückmeldebaum.';
$string['nodenotused'] = 'Keine anderen Knoten im Rückmeldebaum verlinken zu diesem Knoten.';
$string['nodex'] = 'Knoten {$a}';
$string['nodexdelete'] = 'Knoten {$a} löschen';
$string['nodexfalsefeedback'] = 'Knoten {$a} falsches Feedback';
$string['nodextruefeedback'] = 'Knoten {$a} richtiges Feedback';
$string['nodexwhenfalse'] = 'Knoten {$a} wenn falsch';
$string['nodexwhentrue'] = 'Knoten {$a} wenn wahr';
$string['nonempty'] = 'Dies darf nicht leer sein.';
$string['noprtsifnoinputs'] = 'Eine Frage ohne Eingaben kann keine Rückmeldebäume haben.';
$string['notavalidname'] = 'Kein gültiger Name';
$string['optionsnotrequired'] = 'Dieser Eingabetyp erfordert keine Optionen.';
$string['penalty'] = 'Strafe';
$string['penalty_help'] = 'Das Strafsystem zieht diesen Wert von dem Ergebnis jedes Rückmeldebaums für jeden unterschiedlichen und gültigen Versuch ab, der nicht vollständig korrekt ist.';
$string['penalty_link'] = '%%WWWROOT%%/question/type/stack/doc/doc.php/Authoring/Feedback.md';
$string['penaltyerror'] = 'Die Strafe muss ein numerischer Wert zwischen 0 und 1 oder eine Variable sein (die nicht überprüft wird).';
$string['penaltyerror2'] = 'Die Strafe muss leer sein oder ein numerischer Wert zwischen 0 und 1.';
$string['prtcorrectfeedback'] = 'Standard-Feedback für korrekt';
$string['prtheading'] = 'Möglicher Rückmeldebaum: {$a}';
$string['prtmustbesetup'] = 'Dieser Rückmeldebaum muss eingerichtet werden, bevor die Frage gespeichert werden kann.';
$string['prtnamelength'] = 'Rückmeldebaum-Namen dürfen nicht länger als 18 Zeichen sein. \'{$a}\' ist zu lang.';
$string['prtnodesheading'] = 'Knoten des möglichen Rückmeldebaums ({$a})';
$string['prtincorrectfeedback'] = 'Standard-Feedback für falsch';
$string['prtpartiallycorrectfeedback'] = 'Standard-Feedback für teilweise korrekt';
$string['prtremovedconfirmbelow'] = 'Möglicher Rückmeldebaum \'{$a}\' wurde entfernt. Bitte bestätigen Sie dies unten.';
$string['prtremovedconfirm'] = 'Ich bestätige, dass ich diesen möglichen Rückmeldebaum aus dieser Frage entfernen möchte.';
$string['prts'] = 'Mögliche Rückmeldebäume';
$string['prtwillbecomeactivewhen'] = 'Dieser mögliche Rückmeldebaum wird aktiv, wenn der Student geantwortet hat: {$a}';
$string['prtruntimeerror'] = '{$a->prt} hat folgenden Laufzeitfehler erzeugt: {$a->error}';
$string['prtwillberemoved'] = 'Dieser mögliche Rückmeldebaum wird nicht mehr im Fragetext oder im spezifischen Feedback erwähnt. Wenn Sie die Frage jetzt speichern, gehen die Daten über diesen möglichen Rückmeldebaum verloren. Bitte bestätigen Sie, dass Sie dies tun möchten. Alternativ bearbeiten Sie den Fragetext oder das spezifische Feedback, um den Feedback-Platzhalter zurückzusetzen.';
$string['prtruntimescore'] = 'Die Bewertung wurde nicht vollständig zu einem numerischen Wert ausgewertet (überprüfen Sie Variablennamen).';
$string['prtruntimepenalty'] = 'Die Strafe wurde nicht vollständig zu einem numerischen Wert ausgewertet (überprüfen Sie Variablennamen).';
$string['feedbackstyle'] = 'Stil des Rückmeldebaum-Feedbacks';
$string['feedbackstyle_help'] = 'Steuert, wie das Feedback des Rückmeldebaums angezeigt wird.';
$string['feedbackstyle_link'] = '%%WWWROOT%%/question/type/stack/doc/doc.php/Authoring/Potential_response_trees.md';
$string['feedbackstyle0'] = 'Formative';
$string['feedbackstyle1'] = 'Standard';
$string['feedbackstyle2'] = 'Kompakt';
$string['feedbackstyle3'] = 'Nur Symbol';
$string['questionnote'] = 'Fragennotiz';
$string['questionnote_help'] = 'Die Fragennotiz ist CASText. Der Zweck einer Fragennotiz besteht darin, zwischen zufälligen Varianten einer Frage zu unterscheiden. Zwei Fragevarianten sind gleich, wenn und nur wenn die Fragennotizen gleich sind. In späteren Analysen ist es sehr hilfreich, eine aussagekräftige Fragennotiz zu hinterlassen.';
$string['questiondescription'] = 'Fragebeschreibung';
$string['questiondescription_help'] = 'Die Fragebeschreibung ist CASText. Der Zweck einer Fragebeschreibung besteht darin, einen sinnvollen Ort zur Diskussion der Frage zu bieten. Diese ist für Studenten nicht zugänglich.';
$string['questionnote_link'] = '%%WWWROOT%%/question/type/stack/doc/doc.php/Authoring/Question_note.md';
$string['questionnote_missing'] = 'Die Fragennotiz ist leer. Bitte fügen Sie eine aussagekräftige Fragennotiz (Zusammenfassung) hinzu.';
$string['questionnotempty'] = 'Die Fragennotiz darf nicht leer sein, wenn rand() in den Fragevariablen erscheint. Die Fragennotiz wird verwendet, um zwischen verschiedenen zufälligen Varianten der Frage zu unterscheiden.';
$string['questionsimplify'] = 'Fragen-Ebene vereinfachen';
$string['questionsimplify_help'] = 'Setzt die globale Variable "simp" innerhalb von Maxima für die gesamte Frage.';
$string['questionsimplify_link'] = '%%WWWROOT%%/question/type/stack/doc/doc.php/CAS/Maxima.md#Vereinfachung';
$string['questionwarnings'] = 'Fragenwarnungen';
$string['questionwarnings_help'] = 'Fragenwarnungen sind Probleme, die Sie möglicherweise ansprechen möchten, die jedoch keine offensichtlichen Fehler sind.';
$string['questiontext'] = 'Fragentext';
$string['questiontext_help'] = 'Der Fragentext ist CASText. Dies ist die "Frage", die der Student tatsächlich sieht. Sie müssen Eingabeelemente und die Validierungsstrings in diesem Feld, und nur in diesem Feld, platzieren. Zum Beispiel mit [[input:ans1]] [[validation:ans1]].';
$string['questiontext_link'] = '%%WWWROOT%%/question/type/stack/doc/doc.php/Authoring/CASText.md#question_text';
$string['questiontextnonempty'] = 'Der Fragentext darf nicht leer sein.';
$string['questiontextmustcontain'] = 'Der Fragentext muss das Token \'{$a}\' enthalten.';
$string['questiontextonlycontain'] = 'Der Fragentext sollte das Token \'{$a}\' nur einmal enthalten.';
$string['questiontextplaceholderswhitespace'] = 'Platzhalter dürfen keine Leerzeichen enthalten. Dieser scheint dies zu tun: \'{$a}\'.';
$string['questiontextfeedbackonlycontain'] = 'Der Fragentext in Kombination mit dem spezifischen Feedback sollte das Token \'{$a}\' nur einmal enthalten.';
$string['questiontextfeedbacklanguageproblems'] = 'Es gibt Inkonsistenzen in den Feedback-Tags zwischen den Sprachen.';
$string['questionvalue'] = 'Fragebewertung';
$string['questionvaluepostive'] = 'Die Fragebewertung muss nicht negativ sein.';
$string['questionvariables'] = 'Fragevariablen';
$string['questionvariables_help'] = 'Dieses Feld ermöglicht es Ihnen, CAS-Variablen zu definieren und zu manipulieren, z.B. um zufällige Varianten zu erstellen. Diese sind allen anderen Teilen der Frage zugänglich.';
$string['questionvariables_link'] = '%%WWWROOT%%/question/type/stack/doc/doc.php/Authoring/Variables.md#Fragevariablen';
$string['questionvariablevalues'] = 'Werte der Fragevariablen';
$string['quiet'] = 'Still';
$string['quiet_help'] = 'Wenn auf Ja gesetzt, wird jedes Feedback, das automatisch von den Antworttests generiert wird, unterdrückt und nicht dem Studenten angezeigt. Die Feedbackfelder in den Verzweigungen werden von dieser Option nicht beeinflusst.';
// Das Symbol fa-volume-off ist eigentlich nicht sehr gut.
$string['quiet_icon_true'] = '<span style="font-size: 1.25em; color:red;"><i class="fa fa-microphone-slash" aria-hidden="true"></i></span>';
$string['quiet_icon_false'] = '<span style="font-size: 1.25em; color:blue;"><i class="fa fa-commenting-o"></i></span>';
$string['renamequestionparts'] = 'Teile der Frage umbenennen';
$string['requiredfield'] = 'Dieses Feld ist erforderlich!';
$string['requirelowestterms'] = 'Niedrigste Terme fordern';
$string['requirelowestterms_help'] = 'Wenn diese Option auf Ja gesetzt ist, müssen alle Koeffizienten oder andere rationale Zahlen in einem Ausdruck in niedrigster Form geschrieben sein. Andernfalls wird die Antwort als ungültig abgelehnt.';
$string['requirelowestterms_link'] = '%%WWWROOT%%/question/type/stack/doc/doc.php/Authoring/Inputs.md#Niedrigste_Terme';
$string['sans'] = 'SAns';
$string['sans_help'] = 'Dies ist das erste Argument für die Antworttestfunktion. Bei asymmetrischen Tests wird dies als "Antwort des Studenten" betrachtet, obwohl es jeder gültige CAS-Ausdruck sein kann und von den Fragevariablen oder den Feedback-Variablen abhängen kann.';
$string['sans_link'] = '%%WWWROOT%%/question/type/stack/doc/doc.php/Authoring/Answer_Tests/index.md';
$string['sansrequired'] = 'SAns darf nicht leer sein.';
$string['stop'] = '[stop]';
$string['score'] = 'Punktzahl';
$string['scoreerror'] = 'Die Punktzahl muss ein numerischer Wert zwischen 0 und 1 oder eine Variable sein (die nicht überprüft wird).';
$string['scoremode'] = 'Modus';
$string['specificfeedback'] = 'Spezifisches Feedback';
$string['specificfeedback_help'] = 'Standardmäßig wird das Feedback für jeden möglichen Rückmeldebaum in diesem Block angezeigt. Es kann in den Fragetext verschoben werden, in diesem Fall hat Moodle weniger Kontrolle darüber, wann es durch verschiedene Verhaltensweisen angezeigt wird.';
$string['sqrtsign'] = 'Wurzelzeichen für Quadratwurzel';
$string['sqrtsign_help'] = 'Steuert, wie Wurzelzeichen angezeigt werden.';
$string['sqrtsign_link'] = '%%WWWROOT%%/question/type/stack/doc/doc.php/Authoring/Options.md#wurzel';
$string['strictsyntax'] = 'Strikte Syntax';
$string['strictsyntax_help'] = 'Diese Option wird nicht mehr verwendet und wird entfernt.';
$string['strictsyntax_link'] = '%%WWWROOT%%/question/type/stack/doc/doc.php/';
$string['strlengtherror'] = 'Diese Zeichenkette darf nicht länger als 255 Zeichen sein.';
$string['syntaxhint'] = 'Syntaxhinweis';
$string['syntaxhint_help'] = 'Der Syntaxhinweis erscheint im Antwortfeld, wann immer dieses vom Studenten leer gelassen wird.';
$string['syntaxhint_link'] = '%%WWWROOT%%/question/type/stack/doc/doc.php/Authoring/Inputs.md#Syntax_Hinweis';
$string['syntaxattribute'] = 'Hinweismerkmal';
$string['syntaxattribute_help'] = 'Der Syntaxhinweis erscheint als editierbarer Wert oder ein nicht editierbarer Platzhalter.';
$string['syntaxattribute_link'] = '%%WWWROOT%%/question/type/stack/doc/doc.php/Authoring/Inputs.md#Syntax_Hinweis';
$string['syntaxattributevalue'] = 'Wert';
$string['syntaxattributeplaceholder'] = 'Platzhalter';
$string['nosemicolon'] = 'Sie dürfen Maxima-Ausdrücke hier nicht mit einem Semikolon beenden.';
$string['tans'] = 'TAns';
$string['tans_help'] = 'Dies ist das zweite Argument für die Antworttestfunktion. Bei asymmetrischen Tests wird dies als "Antwort des Lehrers" betrachtet, obwohl es jeder gültige CAS-Ausdruck sein kann und von den Fragevariablen oder den Feedback-Variablen abhängen kann.';
$string['tans_link'] = '%%WWWROOT%%/question/type/stack/doc/doc.php/Authoring/Answer_Tests/index.md';
$string['tansrequired'] = 'TAns darf nicht leer sein.';
$string['teachersanswer'] = 'Musterantwort';
$string['teachersanswer_help'] = 'Der Lehrer muss für jede Eingabe eine Musterantwort angeben. Dies muss ein gültiger Maxima-String sein und kann aus den Fragevariablen gebildet werden.';
$string['teachersanswer_link'] = '%%WWWROOT%%/question/type/stack/doc/doc.php/Authoring/Inputs.md#Musterantwort';
$string['testoptions'] = 'Testoptionen';
$string['testoptions_help'] = 'Dieses Feld ermöglicht es Antworttests, eine Option zu akzeptieren, z. B. eine Variable oder eine numerische Präzision.';
$string['testoptions_link'] = '%%WWWROOT%%/question/type/stack/doc/doc.php/Authoring/Answer_Tests/index.md';
$string['testoptionsinvalid'] = 'Die Testoptionen sind ungültig: {$a}';
$string['testoptionsrequired'] = 'Testoptionen sind für diesen Test erforderlich.';
$string['description'] = 'Beschreibung';
$string['description_err'] = 'Die Knotenbeschreibung ist länger als 255 Zeichen.';
$string['testoptions_help'] = 'Dieses Feld ermöglicht es dem Lehrer, den Zweck des Tests aufzuzeichnen';
$string['testoptions_link'] = '%%WWWROOT%%/question/type/stack/doc/doc.php/Authoring/Potential_response_trees.md';
$string['truebranch'] = 'Wahrer Zweig';
$string['truebranch_help'] = 'Diese Felder kontrollieren was passiert, wenn die Antwortüberprüfung positiv ausfällt
### Mod und Punkte
Wie die Bepunktung angepasst wird. "=" setzt die Punkte auf einen bestimmten Wert. "+/-" addieren oder subtrahieren Punkte von der aktuellen Summe.

### Abzüge
Im adaptiven oder interaktiven Modus, ziehe so viele Punkte ab.

### Nächster
Soll zu einem nächsten Knoten gesprungen werden, falls ja zu welchen, ansonsten stoppe.

### Antworthinweis
Dieses Tag dient zur Berichterstattung. Es bestimmt den eindeutigen Pfad durch den Baum und das Ergebnis jeder Antwort. Es wird automatisch erzeugt, kann aber auch manuell zu etwas Sinnvollem geändert werden.
';
$string['variantsselectionseed'] = 'Zufallsgruppe';
$string['variantsselectionseed_help'] = 'Normalerweise kann dies leer gelassen werden. Falls sie aber für zwei verschiedene Aufgaben in einem Test den gleichen Random Seed verwenden wollen, schreiben sie in dieses Feld für beide Aufgaben die gleiche Zeichenkette. Die Random Seeds der beiden Aufgaben werden damit dann synchronisiert.';
$string['verifyquestionandupdate'] = 'Überprüfe den Aufgabentext und aktualisieren die Felder';
$string['youmustconfirm'] = 'Sie müssen hier bestätigen.';

// Von Eingabeelementen verwendete Zeichenketten.
$string['booleangotunrecognisedvalue'] = 'Ungültige Eingabe.';
$string['dropdowngotunrecognisedvalue'] = 'Ungültige Eingabe.';
$string['pleaseananswerallparts'] = 'Bitte beantworten Sie alle Teile der Frage.';
$string['pleasecheckyourinputs'] = 'Bitte überprüfen Sie, ob das, was Sie eingegeben haben, wie erwartet interpretiert wurde.';
$string['singlechargotmorethanone'] = 'Sie können hier nur ein einziges Zeichen eingeben.';

$string['true'] = 'Wahr';
$string['false'] = 'Falsch';
$string['notanswered'] = '(Meine Auswahl löschen)';
$string['ddl_runtime'] = 'Die Eingabe hat den folgenden Laufzeitfehler erzeugt, der Sie daran hindert, zu antworten. Bitte kontaktieren Sie Ihren Lehrer.';
$string['ddl_empty'] = 'Für dieses Dropdown wurden keine Optionen bereitgestellt.';
$string['ddl_nocorrectanswersupplied'] = 'Der Lehrer hat nicht mindestens eine korrekte Antwort angegeben.';
$string['ddl_duplicates'] = 'Doppelte Werte wurden bei der Generierung der Eingabeoptionen gefunden.';
$string['ddl_badanswer'] = 'Das Feld für die Musterantwort dieser Eingabe ist fehlerhaft: <code>{$a}</code>.';
$string['ddl_unknown'] = 'STACK hat <code>{$a}</code> erhalten, aber dies ist nicht als Option vom Lehrer aufgelistet.';

$string['teacheranswershow'] = 'Die Antwort {$a->display}, die als {$a->value} eingegeben werden kann, wäre korrekt.';
$string['teacheranswershow_disp'] = 'Die Antwort {$a->display} wäre korrekt.';
$string['teacheranswershow_mcq'] = 'Eine korrekte Antwort ist: {$a->display}';
$string['teacheranswershownotes'] = 'Für diese Eingabe wird keine korrekte Antwort bereitgestellt.';
$string['teacheranswerempty'] = 'Diese Eingabe kann leer gelassen werden.';

$string['questiontextlanguages'] = 'Die in Ihrer Frage gefundenen Sprachkennzeichnungen sind: {$a}.';
$string['languageproblemsexist'] = 'Es gibt potenzielle Sprachprobleme in Ihrer Frage.';
$string['languageproblemsmissing'] = 'Das Sprachkennzeichen {$a->lang} fehlt in den folgenden: {$a->missing}.';
$string['languageproblemsextra'] = 'Das Feld {$a->field} enthält die folgenden Sprachen, die nicht im Fragetext vorkommen: {$a->langs}.';

$string['alttextmissing']    = 'One or more images appears to have a missing or empty \'alt\' tag in "{$a->field}" ({$a->num}).';
$string['todowarning'] = 'Sie haben ungelöste Todo-Blöcke in "{$a->field}".';

// Admin-Einstellungen.
$string['settingajaxvalidation'] = 'Sofortige Validierung';
$string['settingajaxvalidation_desc'] = 'Wenn diese Einstellung aktiviert ist, wird die aktuelle Eingabe des Studenten validiert, sobald er mit dem Tippen pausiert. Dies bietet eine bessere Benutzererfahrung, kann jedoch die Serverlast erhöhen.';
$string['settingcasdebugging'] = 'CAS-Debugging';
$string['settingcasdebugging_desc'] = 'Ob Debugging-Informationen über die CAS-Verbindung gespeichert werden sollen.';
$string['settingcasmaximaversion'] = 'Maxima-Version';
$string['settingcasmaximaversion_desc'] = 'Die verwendete Version von Maxima.';
$string['settingcasresultscache'] = 'CAS-Ergebnis-Caching';
$string['settingcasresultscache_db'] = 'Cache in der Datenbank';
$string['settingcasresultscache_desc'] = 'Diese Einstellung bestimmt, ob Aufrufe an das CAS gecacht werden. Diese Einstellung sollte aktiviert sein, es sei denn, Sie entwickeln am Maxima-Code. Der aktuelle Zustand des Caches wird auf der Healthcheck-Seite angezeigt. Wenn Sie Ihre Einstellungen ändern, z.B. den gnuplot-Befehl, müssen Sie den Cache löschen, bevor Sie die Auswirkungen dieser Änderungen sehen können.';
$string['settingcasresultscache_none'] = 'Nicht cachen';
$string['settingcastimeout'] = 'CAS-Verbindungs-Timeout';
$string['settingcastimeout_desc'] = 'Das Timeout, das beim Versuch, eine Verbindung zu Maxima herzustellen, verwendet wird.';
$string['settingcaspreparse'] = 'Alle Codes vor dem Senden an Maxima vorverarbeiten.';
$string['settingcaspreparse_desc'] = 'Wir empfehlen, dass alle Codes in Fragevariablen usw., selbst von vertrauenswürdigen Lehrern, vorverarbeitet werden, um potenziell bösartige Muster zu erkennen. Dies ist besonders wichtig, wenn importierte Fragen von bekannten Quellen akzeptiert werden. Es ist jedoch möglich, dass diese Vorverarbeitung ein Timeout erreicht und es notwendig wäre, diese Überprüfung (vorübergehend) zu deaktivieren, um aus einer potenziellen Sackgasse herauszukommen. Dieser Code wird noch getestet und entwickelt und diese Einstellung wird in zukünftigen Versionen entfernt, um sicherzustellen, dass diese Vorverarbeitung immer angewendet wird.';
$string['settingcaspreparse_true'] = 'Immer vorverarbeiten';
$string['settingcaspreparse_false'] = 'Nicht vorverarbeiten (nicht empfohlen)';
$string['settingdefaultinputoptions'] = 'Standard-Eingabeoptionen';
$string['settingdefaultinputoptions_desc'] = 'Wird verwendet, wenn eine neue Frage erstellt oder eine neue Eingabe zu einer bestehenden Frage hinzugefügt wird.';
$string['settingdefaultquestionoptions'] = 'Standard-Fragenoptionen';
$string['settingdefaultquestionoptions_desc'] = 'Wird verwendet, wenn eine neue Frage erstellt wird.';
$string['settingmathsdisplay'] = 'Mathematik-Filter';
$string['settingmathsdisplay_mathjax'] = 'MathJax';
$string['settingmathsdisplay_tex'] = 'Moodle TeX-Filter';
$string['settingmathsdisplay_maths'] = 'Alter OU-Mathematik-Filter';
$string['settingmathsdisplay_oumaths'] = 'Neuer OU-Mathematik-Filter';
$string['settingmathsdisplay_desc'] = 'Die Methode zur Anzeige von Mathematik. Wenn Sie MathJax auswählen, müssen Sie den Anweisungen auf der Healthcheck-Seite folgen, um es einzurichten. Wenn Sie einen Filter auswählen, müssen Sie sicherstellen, dass dieser Filter auf der Konfigurationsseite "Filter verwalten" aktiviert ist.';
$string['settingsmathsdisplayheading'] = 'Optionen zur Anzeige von Mathematik';
$string['settingsmaximasettings'] = 'Verbindung zu Maxima';
$string['settingparsercacheinputlength'] = 'Geparste Ausdrücke länger als cachen';
$string['settingparsercacheinputlength_desc'] = 'Der Ausdrucksparser wird bei langen Ausdrücken (zum Beispiel komplizierte Fragevariablen) recht langsam. Daher cachen wir das Ergebnis des Parsens von Ausdrücken, die länger als diese Grenze sind. Idealerweise sollte diese Einstellung so gesetzt sein, dass die Cache-Abfrage etwa so lange dauert wie das Parsen. 50 Zeichen sind eine fundierte Schätzung dafür. Wenn auf 0 gesetzt, ist der Cache deaktiviert.';
$string['settingplatformtype'] = 'Plattformtyp';
$string['settingplatformtype_desc'] = 'STACK muss wissen, auf welchem Betriebssystem es läuft. Die Option "Server" bietet eine bessere Leistung, erfordert jedoch das Einrichten eines zusätzlichen Servers. Die Option "Linux (optimiert)" wird auf der Seite "Maxima optimieren" in der Dokumentation erklärt.';
$string['settingplatformtypelinux'] = 'Linux';
$string['settingplatformtypelinuxoptimised'] = 'Linux (optimiert)';
$string['settingplatformtypewin'] = 'Windows';
$string['settingplatformtypeserver'] = 'Server';
$string['settingplatformtypeserverproxy'] = 'Server (über Proxy)';
$string['settingplatformmaximacommand'] = 'Maxima-Befehl';
$string['settingplatformmaximacommand_desc'] = 'Wenn dies leer ist, wird STACK eine gebildete Vermutung anstellen, wo Maxima zu finden ist. Wenn das fehlschlägt, sollte dies auf den vollständigen Pfad des maxima- oder maxima-optimised-Executables gesetzt werden. Verwenden Sie nur für Entwicklung und Debugging. Nicht auf einem Produktionssystem verwenden: Verwenden Sie optimiert oder besser die Maxima-Pool-Option.';
$string['settingplatformmaximacommandopt'] = 'Optimierter Maxima-Befehl';
$string['settingplatformmaximacommandopt_desc'] = 'Dies sollte auf den vollständigen Pfad des maxima-optimised-Executables gesetzt werden. Erwägen Sie die Verwendung des Timeout-Befehls auf Linux-basierten Systemen. Z.B. timeout --kill-after=10s 10s maxima';
$string['settingplatformmaximacommandserver'] = 'URL des Maxima-Pools';
$string['settingplatformmaximacommandserver_desc'] = 'Für Plattformtyp: Server, muss dies auf die URL des Maxima-Pool-Servlets gesetzt werden.';
$string['settingplatformplotcommand'] = 'Plot-Befehl';
$string['settingplatformplotcommand_desc'] = 'Normalerweise kann dies leer gelassen werden, aber wenn das Plotten von Grafiken nicht funktioniert, müssen Sie möglicherweise hier den vollständigen Pfad zum gnuplot-Befehl angeben.';
$string['settingreplacedollars'] = 'Ersetzen von <code>$</code> und <code></code>';
$string['settingreplacedollars_desc'] = 'Please edit the STACK plugin setting <tt>qtype_stack | maximalibraries</tt>. The following package is not supported: {$a}';
$string['settingserveruserpass'] = 'Server-Benutzername:Passwort';
$string['settingserveruserpass_desc'] = 'Wenn Sie den Plattformtyp: Server verwenden und Ihren Maxima-Pool-Server mit HTTP-Authentifizierung eingerichtet haben, können Sie hier den Benutzernamen und das Passwort eintragen. Das ist etwas sicherer, als sie in die URL zu setzen. Das Format ist Benutzername:Passwort.';
$string['settingusefullinks'] = 'Nützliche Links';
$string['settingmaximalibraries'] = 'Optionale Maxima-Bibliotheken laden:';
$string['settingmaximalibraries_desc'] = 'Dies ist eine durch Kommas getrennte Liste von Maxima-Bibliotheksnamen, die automatisch in Maxima geladen werden. Nur unterstützte Bibliotheksnamen können verwendet werden: "stats, distrib, descriptive, simplex". Wenn Sie die aufgelisteten Bibliotheken ändern, müssen Sie das optimierte Maxima-Image neu erstellen.';
$string['settingmaximalibraries_error'] = 'Bitte bearbeiten Sie die STACK-Plugin-Einstellung <tt>qtype_stack | maximalibraries</tt>. Das folgende Paket wird nicht unterstützt: {$a}';
$string['settingmaximalibraries_failed'] = 'Es scheint, als ob einige der Maxima-Pakete, die Sie angefordert haben, nicht geladen wurden.';

// Strings für das Ersetzen von Dollarzeichen-Skript.
$string['replacedollarscount'] = 'Diese Kategorie enthält {$a} STACK-Fragen.';
$string['replacedollarsin'] = 'Mathematik-Trennzeichen im Feld {$a} korrigiert';
$string['replacedollarsindex'] = 'Kontexte mit STACK-Fragen';
$string['replacedollarsindexintro'] = 'Wenn Sie auf einen der Links klicken, gelangen Sie auf eine Seite, auf der Sie die Fragen nach alten Mathematik-Trennzeichen überprüfen und automatisch korrigieren können. Wenn Sie zu viele Fragen (Tausende) in einem Kontext haben, wird die Menge der Ausgabe wahrscheinlich Ihren Webbrowser überfordern. In diesem Fall fügen Sie einen preview=0-Parameter zur URL hinzu und versuchen Sie es erneut.';
$string['replacedollarsindextitle'] = '$s in Fragetexten ersetzen';
$string['replacedollarsnoproblems'] = 'Keine problematischen Trennzeichen gefunden.';
$string['replacedollarstitle'] = '$s in Fragetexten in {$a} ersetzen';
$string['replacedollarserrors'] = 'Die folgenden Fragen haben Fehler erzeugt.';

// Strings, die vom Skript für Massentests von Fragen verwendet werden.
$string['expand'] = 'Erweitern';
$string['expandtitle'] = 'Fragekategorien anzeigen';
$string['unauthorisedbulktest'] = 'Sie haben keinen geeigneten Zugang zu STACK-Fragen';
$string['bulktestcontinuefromhere'] = 'Erneut ausführen oder fortsetzen, beginnend von hier';
$string['bulktestindexintro'] = 'Wenn Sie auf einen der Links klicken, werden alle Fragetests in allen STACK-Fragen in diesem Kontext ausgeführt';
$string['bulktestindextitle'] = 'Fragetests in Massen ausführen';
$string['bulktestnotests'] = 'Diese Frage hat keine Tests.';
$string['bulktestnogeneralfeedback'] = 'Diese Frage hat kein allgemeines Feedback.';
$string['bulktestnodeployedseeds'] = 'Diese Frage hat zufällige Varianten, aber keine bereitgestellten Seeds.';
$string['bulktestrun'] = 'Alle Fragetests für alle Fragen im System ausführen (langsam, nur Admin)';
$string['bulktesttitle'] = 'Ausführen aller Fragetests in {$a}';
$string['bulktestallincontext'] = 'Alle testen';
$string['testalltitle'] = 'Alle Fragen in diesem Kontext testen';
$string['testallincategory'] = 'Alle Fragen in dieser Kategorie testen';
$string['overallresult'] = 'Gesamtergebnis';
$string['seedx'] = 'Seed {$a}';
$string['testpassesandfails'] = '{$a->passes} Erfolge und {$a->fails} Misserfolge.';

// Strings, die vom Skript für Frage-Test verwendet werden.
$string['addanothertestcase'] = 'Einen weiteren Testfall hinzufügen...';
$string['addatestcase'] = 'Einen Testfall hinzufügen...';
$string['addingatestcase'] = 'Testfall zur Frage {$a} hinzufügen';
$string['alreadydeployed'] = 'Eine Variante, die zu dieser Fragennotiz passt, ist bereits bereitgestellt.';
$string['completetestcase'] = 'Vervollständigen Sie den Rest des Formulars, um einen bestandenen Testfall zu erstellen';
$string['teacheranswercase'] = 'Lehrerantworten als Testfall verwenden';
$string['createtestcase'] = 'Testfall erstellen';
$string['currentlyselectedvariant'] = 'Dies ist die unten gezeigte Variante';
$string['deletetestcase'] = 'Testfall {$a->no} für Frage {$a->question} löschen';
$string['deletetestcaseareyousure'] = 'Sind Sie sicher, dass Sie Testfall {$a->no} für Frage {$a->question} löschen möchten?';
$string['deletethistestcase'] = 'Diesen Testfall löschen.';
$string['deploy'] = 'Einzelne Variante bereitstellen';
$string['deployedprogress'] = 'Varianten werden bereitgestellt';
$string['deployedvariants'] = 'Bereitgestellte Varianten';
$string['deployedvariantsn'] = 'Bereitgestellte Varianten ({$a})';
$string['deploymanybtn'] = 'Anzahl der Varianten bereitstellen:';
$string['deploymanyerror'] = 'Fehler in der Benutzereingabe: "{$a->err}" Varianten können nicht bereitgestellt werden.';
$string['deploysystematicbtn'] = 'Seeds von 1 bis bereitstellen: ';
$string['deployduplicateerror'] = 'Doppelte Fragennotizen in den bereitgestellten Varianten erkannt. Wir empfehlen dringend, jede Fragennotiz nur einmal bereitzustellen, da Sie sonst Schwierigkeiten haben werden, aussagekräftige Statistiken zu sammeln, wenn Sie nach Variante gruppieren. Bitte erwägen Sie, einige Varianten mit doppelten Notizen zu löschen.';
$string['deploytoomanyerror'] = 'STACK versucht, in einer Anfrage maximal 100 neue Varianten bereitzustellen. Keine neuen Varianten bereitgestellt.';
$string['deploymanynonew'] = 'Zu viele wiederholte vorhandene Fragennotizen generiert.';
$string['deploymanynotes'] = 'Versuch, automatisch eine Anzahl von Varianten bereitzustellen. STACK gibt auf, wenn es 10 fehlgeschlagene Versuche gibt, eine neue Fragennotiz zu generieren, oder wenn ein Fragetest fehlschlägt.';
$string['deploymanysuccess'] = 'Anzahl der erfolgreich erstellten, getesteten und bereitgestellten neuen Varianten: {$a->no}.';
$string['deployoutoftime'] = 'Zeitlimit überschritten durch Verwendung von ca. {$a->time} Sekunden. Bitte versuchen Sie es erneut, um mehr bereitzustellen.';
$string['deployremoveall'] = 'Alle Varianten zurücknehmen';
$string['deploytestall'] = 'Alle Tests für alle bereitgestellten Varianten ausführen (langsam)';
$string['deployfromlist'] = 'Positive ganze Zahlen auflisten, eine in jeder Zeile.';
$string['deployfromlistexisting'] = 'Aktuelle Seeds:';
$string['deployfromlistbtn'] = 'Varianten entfernen und neu von der Liste bereitstellen';
$string['deployfromlisterror'] = 'Ein Fehler wurde in Ihrer Liste von Ganzzahlen festgestellt, daher wurden keine Änderungen an der Liste der bereitgestellten Varianten vorgenommen.';
$string['editingtestcase'] = 'Testfall {$a->no} für Frage {$a->question} bearbeiten';
$string['editthistestcase'] = 'Diesen Testfall bearbeiten.';
$string['confirmthistestcase'] = 'Aktuelles Testverhalten bestätigen.';
$string['expectedanswernote'] = 'Erwartete Antwortnotiz';
$string['expectedoutcomes'] = 'Erwartete PRT-Ergebnisse: [verwendete Eingaben]';
$string['expectedpenalty'] = 'Erwartete Strafe';
$string['expectedscore'] = 'Erwartete Punktzahl';
$string['inputdisplayed'] = 'Angezeigt als';
$string['inputentered'] = 'Eingegebener Wert';
$string['inputexpression'] = 'Testeingabe';
$string['inputname'] = 'Eingabename';
$string['inputstatus'] = 'Status';
$string['inputstatusname'] = 'Leer';
$string['inputstatusnameinvalid'] = 'Ungültig';
$string['inputstatusnamevalid'] = 'Gültig';
$string['inputstatusnamescore'] = 'Punktzahl';
$string['notestcasesyet'] = 'Bisher wurden noch keine Testfälle hinzugefügt.';
$string['penalty'] = 'Strafe';
$string['prtname'] = 'PRT-Name';
$string['questiondoesnotuserandomisation'] = 'Diese Frage verwendet keine Randomisierung.';
$string['questionnotdeployedyet'] = 'Noch keine Varianten dieser Frage wurden bereitgestellt.';
$string['questionpreview'] = 'Fragevorschau';
$string['questiontestempty'] = 'Leere Fragetests sind nicht erlaubt!';
$string['questiontests'] = 'Fragetests';
$string['questiontestsfor'] = 'Fragetests für Seed {$a}';
$string['questiontestspass'] = 'Alle Fragetests bestanden.';
$string['questiontestsdefault'] = '(Standard)';
$string['runquestiontests'] = 'STACK-Fragendashboard';
$string['runquestiontests_help'] = 'Das Dashboard führt Fragetests durch, die Fragen auf das erwartete Verhalten testen, wie vom Lehrer ausgedrückt, und bereitgestellte Varianten stellen sicher, dass zufällige Versionen, die ein Student sieht, vorab gegen die Fragetests getestet werden. Dies sind Werkzeuge, die Ihnen helfen, zuverlässige Fragen zu erstellen und zu testen, und sollten in allen Fällen verwendet werden, in denen eine Frage von Studenten verwendet wird. Das Dashboard hat auch zahlreiche weitere STACK-spezifische Funktionen.';
$string['runquestiontests_alert'] = 'Frage hat keine Tests oder Varianten.';
$string['runquestiontests_auto'] = 'Automatisch einen Testfall hinzufügen, der davon ausgeht, dass die Eingabe des Lehrers volle Punktzahl erhält. Bitte überprüfen Sie die Antwortnotiz sorgfältig.';
$string['runquestiontests_autoprompt'] = 'Testfall hinzufügen unter der Annahme, dass die Eingabe des Lehrers volle Punktzahl erhält.';
$string['autotestcase'] = 'Testfall unter der Annahme, dass die Eingabe des Lehrers volle Punktzahl erhält.';
$string['showingundeployedvariant'] = 'Nicht bereitgestellte Variante anzeigen: {$a}';
$string['switchtovariant'] = 'Zu Variante wechseln: ';
$string['testcasexresult'] = 'Testfall {$a->no} {$a->result}';
$string['testingquestion'] = 'Teste Frage {$a}';
$string['testingquestionvariants'] = 'Fragevarianten vorbereiten';
$string['testinputs'] = 'Testeingaben';
$string['testinputsimpwarning'] = 'Bitte beachten Sie, dass Testeingaben immer <em>nicht vereinfacht</em> sind, unabhängig von der Frage- oder PRT-Optionseinstellung. Bitte verwenden Sie <tt>ev(...,simp)</tt>, um Teile oder alle Testeingabeausdrücke zu vereinfachen.';
$string['testthisvariant'] = 'Zu dieser Variante wechseln und testen';
$string['tidyquestionx'] = 'Teile der Frage {$a} umbenennen';
$string['undeploy'] = 'Bereitstellung aufheben';
$string['variant'] = 'Variante';

$string['editquestioninthequestionbank'] = '<i class="fa fa-pencil"></i> Frage bearbeiten';
$string['seethisquestioninthequestionbank'] = '<i class="fa fa-list-alt"></i> In der Fragendatenbank anzeigen';
$string['exportthisquestion'] = '<i class="fa fa-download"></i> Als Moodle XML exportieren';
$string['exportthisquestion_help'] = 'Dies erstellt eine Moodle XML Exportdatei, die nur diese eine Frage enthält. Ein Beispiel, wann dies nützlich ist, ist, wenn Sie denken, dass diese Frage einen Bug in STACK aufzeigt, den Sie den Entwicklern melden möchten.';
$string['tidyquestion'] = '<i class="fa fa-sort-amount-asc"></i> Eingaben und PRTs aufräumen';
$string['sendgeneralfeedback'] = '<i class="fa fa-file-text"></i> Allgemeines Feedback an das CAS senden';

$string['basicquestionreport'] = '<i class="fa fa-bar-chart"></i> Antworten analysieren';
$string['basicquestionreport_help'] = 'Erstellt einen sehr einfachen Bericht über Versuche bei dieser Frage auf dem Server. Nützlich für die Entscheidung, welcher PRT-Test hinzugefügt werden kann, um das Feedback im Licht dessen, was der Student tatsächlich tut, zu verbessern. (Die meisten Fragen werden nur an einem Ort verwendet)';
$string['basicreportraw'] = 'Rohdaten';
$string['basicreportnotes'] = 'Häufigkeit von Antwortnotizen, für jeden PRT, unabhängig von der verwendeten Variante';
$string['basicreportnotessplit'] = 'Häufigkeit von Antwortnotizen, für jeden PRT, getrennt durch |, unabhängig von der verwendeten Variante';
$string['basicreportvariants'] = 'Rohdaten und PRT-Antwortnotizen nach Variante';
$string['basicreportinputsummary'] = 'Rohdaten, unabhängig von der verwendeten Variante';

// Spezifische Strings für Equiv-Eingaben.
$string['equivnocomments'] = 'Es ist Ihnen nicht erlaubt, Kommentare in diesem Eingabetyp zu verwenden. Bitte arbeiten Sie einfach Zeile für Zeile.';
$string['equivfirstline'] = 'Sie haben die falsche erste Zeile in Ihrer Argumentation verwendet!';

// Unterstützungsskripte: CAS-Chat, Gesundheitsprüfung usw.
$string['all'] = 'Alle';
$string['chat'] = 'An das CAS senden';
$string['savechat'] = 'Zurück zur Frage speichern';
$string['savechatmsg'] = 'Fragevariablen und allgemeines Feedback wurden zurück in die Frage gespeichert.';
$string['castext'] = 'CAS-Text';
$string['chat_desc'] = 'Das <a href="{$a->link}">CAS-Chat-Skript</a> ermöglicht es Ihnen, die Verbindung zum CAS zu testen und Maxima-Syntax auszuprobieren.';
$string['chatintro'] = 'Diese Seite ermöglicht es, CAS-Text direkt auszuwerten. Es ist ein einfaches Skript, das ein nützliches minimales Beispiel ist und eine praktische Möglichkeit bietet, um zu überprüfen, ob das CAS funktioniert, und um verschiedene Eingaben zu testen. Das erste Textfeld ermöglicht es, Variablen zu definieren, das zweite ist für den CAS-Text selbst.';
$string['chattitle'] = 'Die Verbindung zum CAS testen';
$string['clearedthecache'] = 'Der CAS-Cache wurde geleert.';
$string['clearingcachefiles'] = 'Leeren von gecachten STACK-Plot-Dateien {$a->done}/{$a->total}';
$string['clearingthecache'] = 'Leeren des Caches';
$string['clearthecache'] = 'Den Cache leeren';
$string['healthcheck'] = 'STACK-Gesundheitscheck';
$string['healthcheck_desc'] = 'Das <a href="{$a->link}">Healthcheck-Skript</a> hilft Ihnen zu überprüfen, ob alle Aspekte von STACK ordnungsgemäß funktionieren.';
$string['healthcheckcache_db'] = 'CAS-Ergebnisse werden in der Datenbank gecacht.';
$string['healthcheckcache_none'] = 'CAS-Ergebnisse werden nicht gecacht.';
$string['healthcheckcache_otherdb'] = 'CAS-Ergebnisse werden in einer anderen Datenbank gecacht.';
$string['healthcheckcachestatus'] = 'Der Cache enthält derzeit {$a} Einträge.';
$string['healthcheckconfigintro1'] = 'Maxima wurde im folgenden Verzeichnis gefunden und wird verwendet:';
$string['healthcheckconnect'] = 'Versuch, eine Verbindung zum CAS herzustellen';
$string['healthcheckconnectintro'] = 'Wir versuchen, den folgenden CAS-Text auszuwerten:';
$string['healthcheckfilters'] = 'Bitte stellen Sie sicher, dass der {$a->filter} auf der Seite <a href="{$a->url}">Filter verwalten</a> aktiviert ist.';
$string['healthchecknombstring'] = 'STACK v4.3 und spätere Versionen benötigen das PHP-Modul mbstring, das fehlt. Bitte lesen Sie die Installationsdokumente.';
$string['healthchecklatex'] = 'Überprüfen Sie, ob LaTeX korrekt konvertiert wird';
$string['healthchecklatexintro'] = 'STACK generiert dynamisch LaTeX und ermöglicht Lehrern, LaTeX in Fragen zu schreiben. Es wird davon ausgegangen, dass LaTeX von einem Moodle-Filter konvertiert wird. Hier sind Beispiele für angezeigte und eingebettete Ausdrücke in LaTeX, die in Ihrem Browser korrekt erscheinen sollten. Probleme hier weisen auf falsche Moodle-Filtereinstellungen hin, nicht auf Fehler in STACK selbst.';
$string['healthchecklatexmathjax'] = 'STACK verlässt sich auf den Moodle MathJax-Filter. Eine Alternative besteht darin, JavaScript-Code zum zusätzlichen HTML von Moodle hinzuzufügen. Weitere Details finden Sie in den STACK-Installationsdokumenten.';
$string['healthcheckmathsdisplaymethod'] = 'Verwendete Methode zur Anzeige von Mathematik: {$a}.';
$string['healthcheckmaximabat'] = 'Die Datei maxima.bat fehlt';
$string['healthcheckmaximabatinfo'] = 'Dieses Skript hat versucht, die Datei maxima.bat automatisch von "C:\Programme\Maxima-1.xx.y\bin" nach "{$a}\stack" zu kopieren. Das scheint jedoch nicht funktioniert zu haben. Bitte kopieren Sie diese Datei manuell.';
$string['healthcheckproxysettings'] = '<strong>Warnung:</strong> Moodle ist so eingestellt, dass ein Proxy-Server verwendet wird, aber Aufrufe an Maxima umgehen diesen. Wechseln Sie die Plattform von "Server" zu "Server (über Proxy)", um Aufrufe über den Proxy-Server zu leiten, oder fügen Sie den Maxima-Server zu $CFG->proxybypass hinzu, um das Umgehen explizit zu machen. STACK sollte auch funktionieren, wenn Sie keine Änderung vornehmen, aber Moodle-Proxy-Einstellungen werden in einer späteren Version durchgesetzt.';
$string['healthchecksamplecas'] = 'The derivative of {@ x^4/(1+x^4) @} is \[ \frac{d}{dx} \frac{x^4}{1+x^4} = {@ diff(x^4/(1+x^4),x) @}. \]';
$string['healthcheckconnectunicode'] = 'Trying to send unicode to the CAS';
$string['healthchecksamplecasunicode'] = 'Confirm if unicode is supported: \(\forall\) should be displayed {@unicode(8704)@}.';
$string['healthchecksampledisplaytex'] = '\[\sum_{n=1}^\infty \frac{1}{n^2} = \frac{\pi^2}{6}.\]';
$string['healthchecksampleinlinetex'] = '\(\sum_{n=1}^\infty \frac{1}{n^2} = \frac{\pi^2}{6}\).';
$string['healthcheckmaximalocal'] = 'Contents of the maximalocal file';
$string['healthcheckplots'] = 'Graph plotting';
$string['healthcheckplotsintro'] = 'There should be two different plots.  If two identical plots are seen then this is an error in naming the plot files. If no errors are returned, but a plot is not displayed then one of the following may help.  (i) check read permissions on the two temporary directories. (ii) change the options used by GNUPlot to create the plot. Currently there is no web interface to these options.';
$string['healthchecksampleplots'] = 'Two example plots below.  {@plot([x^4/(1+x^4),diff(x^4/(1+x^4),x)],[x,-3,3])@} {@plot([sin(x),x,x^2,x^3],[x,-3,3],[y,-3,3],grid2d)@}  A third, smaller, plot should be displayed below with traditional axes. {@plot([x,2*x^2-1,x*(4*x^2-3),8*x^4-8*x^2+1,x*(16*x^4-20*x^2+5),(2*x^2-1)*(16*x^4-16*x^2+1)],[x,-1,1],[y,-1.2,1.2],[box, false],[yx_ratio, 1],[axes, solid],[xtics, -3, 1, 3],[ytics, -3, 1, 3],[size,250,250])@}';
$string['healthchecksstackmaximaversion'] = 'Maxima version';
$string['healthchecksstackmaximaversionfixoptimised'] = 'Please <a href="{$a->url}">rebuild your optimised Maxima executable</a>.';
$string['healthchecksstackmaximaversionfixserver'] = 'Please rebuild the Maxima code on your MaximaPool server.';
$string['healthchecksstackmaximaversionfixunknown'] = 'It is not really clear how that happened. You will need to debug this problem yourself.  Start by clearing the CAS cache.';
$string['healthchecksstackmaximanotupdated'] = 'It seems that STACK has not been properly update. Please visit the <a href="{$a}">System administration -> Notifications page</a>.';
$string['healthchecksstackmaximatooold'] = 'So old the version is unknown!';
$string['healthchecksstackmaximaversionmismatch'] = 'The version of the STACK-Maxima libraries being used ({$a->usedversion}) does not match what is expected ({$a->expectedversion}) by this version of the STACK question type. {$a->fix}';
$string['healthchecksstackmaximaversionok'] = 'Correct and expected STACK-Maxima library version being used ({$a->usedversion}).';
$string['healthchecksstacklibrariesworking'] = 'Maxima optional libraries';
$string['healthchecksstacklibrariesworkingok'] = 'Maxima optional libraries appear to be actually loading correctly.';
$string['healthchecksstacklibrariesworkingsession'] = 'Checking the optional maxima libraries threw the following error: {$a->err}';
$string['healthchecksstacklibrariesworkingfailed'] = 'The following optional maxima library/libraries appear not to load: {$a->err}.  Try recreating your Maxima image.';
$string['healthuncached'] = 'Uncached CAS call';
$string['healthuncachedintro'] = 'This section always sends a genuine call to the CAS, regardless of the current cache settings.  This is needed to ensure the connection to the CAS is really currently working.';
$string['healthuncachedstack_CAS_ok'] = 'CAS returned data as expected.  You have a live connection to the CAS.';
$string['healthuncachedstack_CAS_not'] = 'CAS returned some data as expected, but there were errors.';
$string['healthuncachedstack_CAS_version'] = 'Expected Maxima version : "{$a->expected}".  Actual Maxima version: {$a->actual}.';
$string['healthuncachedstack_CAS_versionnotchecked'] = 'You have chosen the "default" version of Maxima, so no Maxima version checking is being done.  Your raw connection is actually using version {$a->actual}.';
$string['healthuncachedstack_CAS_calculation'] = 'Expected CAS calculation : {$a->expected}.  Actual CAS calculation: {$a->actual}.';
$string['healthuncachedstack_CAS_trigsimp'] = 'The function "trigsimp" is not working.  Perhaps you need to install the maxima-share package on your system as well?';
$string['healthunabletolistavail'] = 'Platform type not currently set to "linux", so unable to list available versions of Maxima.';
$string['healthautomaxopt'] = 'Automatically create an optimised Maxima image';
$string['healthautomaxoptintro'] = 'For best performance we need to optimize maxima on a linux machine.  Use the plugin "healthcheck" page and see the documentation on this issue.';
$string['healthautomaxopt_succeeded'] = 'Create Optimised Maxima Image SUCCEEDED';
$string['healthautomaxopt_failed'] = 'Create Optimised Maxima Image FAILED : [{$a->errmsg}]';
$string['healthautomaxopt_ok'] = 'Maxima image created at: <tt>{$a->command}</tt>';
$string['healthautomaxopt_notok'] = 'Maxima image not created automatically.';
$string['healthautomaxopt_nolisp'] = 'Unable to determine LISP version, so Maxima image not created automatically.';
$string['healthautomaxopt_nolisprun'] = 'Unable to automatically locate lisp.run.  Try "sudo updatedb" from the command line and refer to the optimization docs.';
$string['healthcheckcreateimage'] = 'Create Maxima image';
$string['healthcheckmaximaavailable'] = 'Versions of Maxima available on this server';
$string['healthcheckpass'] = 'The healthcheck passed without detecting any issues.  However, please read the detail below carefully.  Not every problem can be automatically detected.';
$string['healthcheckfail'] = 'The healthcheck detected serious problems.  Please read the diagnostic information below for more detail.';
$string['healthcheckfaildocs'] = 'Detailed notes and trouble-shooting advice is given in the documentation under <a href="{$a->link}">Installation > Testing installation</a>.';
$string['stackInstall_replace_dollars_desc'] = 'The <a href="{$a->link}">fix maths delimiters script</a> can be used to replace old-style delimiters like <code>@...@</code>, <code>$...$</code> and <code>$$...$$</code> in your questions with the new recommended <code>{@...@}</code>, <code>\(...\)</code> and <code>\[...\]</code>.';
$string['stackInstall_testsuite_title'] = 'A test suite for STACK Answer tests';
$string['stackInstall_testsuite_title_desc'] = 'The <a href="{$a->link}">answer-tests script</a> verifies that the answer tests are performing correctly. They are also useful to learn by example how each answer-test can be used.';
$string['stackInstall_testsuite_intro'] = 'This page allows you to see answer test examples, and to test that the STACK answer tests are functioning correctly.  Note that only answer tests can be checked through the web interface.  If the mark is negative this indicates an expected fail, with -1 being a failure due to an expected internal error.';
$string['stackInstall_testsuite_choose'] = 'Please choose an answer test.';
$string['stackInstall_testsuite_pass'] = 'All tests passed!';
$string['stackInstall_testsuite_fail'] = 'Not all tests passed!';
$string['stackInstall_testsuite_failingtests'] = 'Tests that failed';
$string['stackInstall_testsuite_failingupgrades'] = 'Questions which failed on upgrade.';
$string['stackInstall_testsuite_notests'] = 'Questions with no tests: please add some!';
$string['stackInstall_testsuite_nogeneralfeedback'] = 'Questions with no general feedback: students really appreciate worked solutions!';
$string['stackInstall_testsuite_nodeployedseeds'] = 'Questions with random variants, but no deployed seeds';
$string['stackInstall_testsuite_errors'] = 'This question generated the following errors at runtime.';
$string['answertest'] = 'Antworttest';
$string['answertest_help'] = 'Ein Antworttest wird verwendet, um zwei Ausdrücke zu vergleichen und festzustellen, ob sie bestimmte mathematische Kriterien erfüllen.';
$string['answertest_link'] = '%%WWWROOT%%/question/type/stack/doc/doc.php/Authoring/Answer_Tests/index.md';
$string['answertest_ab'] = 'Test';
$string['testsuitecolpassed'] = '?';
$string['studentanswer'] = 'Studentenantwort';
$string['teacheranswer'] = 'Lehrerantwort';
$string['options'] = 'Optionen';
$string['options_short'] = 'Opt';
$string['testsuitefeedback'] = 'Feedback';
$string['testsuitecolerror'] = 'CAS-Fehler';
$string['testsuitecolmark'] = 'Bewertung';
$string['testsuitepass'] = '<span style="color:green;"><i class="fa fa-check"></i></span>';
$string['testsuiteknownfail'] = '<span style="color:orange;"><i class="fa fa-adjust"></i></span>';
$string['testsuiteknownfailmaths'] = '<span style="color:orange;"><i class="fa fa-adjust"></i>!</span>';
$string['testsuitefail'] = '<span style="color:red;"><i class="fa fa-times"></i></span>';
$string['testsuitenotests'] = 'Anzahl der Tests: {$a->no}. ';
$string['testsuiteteststook'] = 'Tests dauerten {$a->time} Sekunden. ';
$string['testsuiteteststookeach'] = 'Durchschnitt pro Test: {$a->time} Sekunden. ';
$string['stackInstall_input_title'] = "Eine Testreihe zur Validierung der Eingabe des Studenten";
$string['stackInstall_input_title_desc'] = 'Das <a href="{$a->link}">Input-Tests-Skript</a> bietet Testfälle dafür, wie STACK mathematische Ausdrücke interpretiert. Sie sind auch nützlich, um durch Beispiele zu lernen.';
$string['stackInstall_input_intro'] = "Diese Seite ermöglicht es Ihnen, zu testen, wie STACK verschiedene Eingaben von Studenten interpretiert. Derzeit wird nur mit den liberalsten Einstellungen überprüft, um eine informelle Syntax zu versuchen und Sterne einzufügen. <br />'V'-Spalten zeigen die Gültigkeit, wie sie von PHP und dem CAS beurteilt wird. V1 = PHP gültig, V2 = CAS gültig.";
$string['phpvalid'] = 'V1';
$string['phpcasstring'] = 'PHP-Ausgabe';
$string['phpsuitecolerror'] = 'PHP-Fehler';
$string['phpvalidatemismatch'] = '[PHP-Validierungsunterschied]';
$string['casvalidatemismatch'] = '[CAS-Validierungsunterschied]';
$string['ansnotemismatch'] = '[Antwortnotizunterschied]';
$string['displaymismatch'] = '[LaTeX-Unterschied]';
$string['casvalid'] = 'V2';
$string['casvalue'] = 'CAS-Wert';
$string['casdisplay'] = 'CAS-Anzeige';
$string['cassuitecolerrors'] = 'CAS-Fehler';

$string['texdisplaystyle'] = 'Gleichung im Anzeigestil';
$string['texinlinestyle'] = 'Gleichung im Inline-Stil';

// Verwendet bei der Validierung von CAS-Aussagen.
$string['stackCas_spaces'] = 'Unerlaubte Leerzeichen gefunden im Ausdruck {$a->expr}.';
$string['stackCas_underscores'] = 'Die folgende Verwendung von Unterstrichen ist nicht erlaubt: {$a}.';
$string['stackCas_percent'] = '% gefunden im Ausdruck {$a->expr}.';
$string['stackCas_missingLeftBracket'] = 'Es fehlt eine linke Klammer <span class="stacksyntaxexample">{$a->bracket}</span> im Ausdruck: {$a->cmd}.';
$string['stackCas_missingRightBracket'] = 'Es fehlt eine rechte Klammer <span class="stacksyntaxexample">{$a->bracket}</span> im Ausdruck: {$a->cmd}.';
$string['stackCas_qmarkoperators'] = 'Fragezeichen sind in Antworten nicht erlaubt.';
$string['stackCas_apostrophe'] = 'Apostrophe sind in Antworten nicht erlaubt.';
$string['stackCas_newline'] = 'Zeilenumbruchzeichen sind in Antworten nicht erlaubt.';
$string['stackCas_forbiddenChar'] = 'CAS-Befehle dürfen die folgenden Zeichen nicht enthalten: {$a->char}.';
$string['stackCas_useinsteadChar'] = 'Bitte ersetzen Sie <span class="stacksyntaxexample">{$a->bad}</span> durch {$a->char}.';
$string['stackCas_finalChar'] = '{$a->char} ist ein ungültiges Endzeichen in {$a->cmd}';
$string['stackCas_MissingStars'] = 'Es scheinen * Zeichen zu fehlen. Vielleicht wollten Sie {$a->cmd} eingeben.';
$string['stackCas_unknownFunction'] = 'Unbekannte Funktion: {$a->forbid} im Term {$a->term}.';
$string['stackCas_noFunction'] = 'Die Verwendung der Funktion {$a->forbid} im Term {$a->term} ist in diesem Kontext nicht erlaubt.';
$string['stackCas_forbiddenFunction'] = 'Verbotene Funktion: {$a->forbid}.';
$string['stackCas_spuriousop'] = 'Unbekannter Operator: {$a->cmd}.';
$string['stackCas_forbiddenOperator'] = 'Verbotener Operator: {$a->forbid}.';
$string['stackCas_forbiddenVariable'] = 'Verbotene Variable oder Konstante: {$a->forbid}.';
$string['stackCas_operatorAsVariable'] = 'Operator {$a->op} als Variable interpretiert, Syntax überprüfen.';
$string['stackCas_redefinitionOfConstant'] = 'Neudefinition von Schlüsselkonstanten ist verboten: {$a->constant}.';
$string['stackCas_unknownFunctionCase'] = 'Eingabe ist groß- und kleinschreibungssensitiv: {$a->forbid} ist eine unbekannte Funktion. Meinten Sie {$a->lower}?';
// Verwendet bei der Validierung von CAS-Aussagen.
$string['stackCas_unknownVariableCase'] = 'Eingabe ist groß- und kleinschreibungssensitiv: {$a->forbid} ist eine unbekannte Variable. Meinten Sie {$a->lower}?';
$string['stackCas_unsupportedKeyword'] = 'Nicht unterstütztes Schlüsselwort: {$a->forbid}.';
$string['stackCas_forbiddenWord'] = 'Der Ausdruck {$a->forbid} ist verboten.';
$string['stackCas_forbiddenntuple'] = 'Koordinaten sind in dieser Eingabe nicht erlaubt.';
$string['stackCas_bracketsdontmatch'] = 'Die Klammern sind im Ausdruck {$a->cmd} falsch verschachtelt.';
$string['stackCas_chained_inequalities']    = 'Es scheint, als hätten Sie "verkettete Ungleichungen", z.B. \(a &lt b &lt c\).  Sie müssen einzelne Ungleichungen mit logischen Operationen wie  \(and\) or \(or\).';
$string['stackCas_backward_inequalities']   = 'Nicht-strenge Ungleichungen, z.B. \( \leq \) or \( \geq \) müssen als <= oder >=.  eingegeben werden. Sie haben {$a->cmd} in Ihrem Ausdruck, was rückwärts ist.';
$string['stackCas_unencpsulated_comma'] = 'Ein Komma in Ihrem Ausdruck erscheint auf seltsame Weise. Kommas werden verwendet, um Elemente in Listen, Mengen usw. zu trennen. Sie müssen einen Dezimalpunkt und kein Komma in Fließkommazahlen verwenden.';
$string['stackCas_unencpsulated_semicolon'] = 'Ein Semikolon (;) in Ihrem Ausdruck erscheint auf seltsame Weise. Semikolons werden verwendet, um Elemente in Listen, Mengen usw. zu trennen.';
$string['stackCas_trigspace'] = 'Um eine trigonometrische Funktion auf ihre Argumente anzuwenden, müssen Sie Klammern und keine Leerzeichen verwenden. Verwenden Sie zum Beispiel {$a->trig} anstelle von.';
$string['stackCas_trigop'] = 'Sie müssen {$a->trig} auf ein Argument anwenden. Es scheint, als hätten Sie {$a->forbid}, was aussieht, als würden Sie {$a->trig} als Variablennamen verwenden.';
$string['stackCas_trigexp'] = 'Sie können keine Potenz einer trigonometrischen Funktion schreiben, indem Sie {$a->forbid} schreiben. Das Quadrat des Wertes von \{$a->identifier}(x) wird als <tt>{$a->identifier}(x)^2</tt> eingegeben. Das Inverse von \{$a->identifier}(x) wird als <tt>a{$a->identifier}(x)</tt> geschrieben und nicht als \{$a->identifier}^{-1}(x).';
$string['stackCas_trigparens'] = 'Wenn Sie eine trigonometrische Funktion auf ihre Argumente anwenden, müssen Sie runde Klammern und keine eckigen Klammern verwenden. Z.B. {$a->forbid}.';
$string['stackCas_triginv'] = 'Inverse trigonometrische Funktionen werden als {$a->goodinv} und nicht als {$a->badinv} geschrieben.';
$string['stackCas_baddotdot'] = 'Die Verwendung von Matrixmultiplikation "." mit Skalar-Floats ist verboten, verwenden Sie stattdessen normale Multiplikation "*" für dasselbe Ergebnis.';
$string['stackCas_badLogIn'] = 'Sie haben den Ausdruck <tt>In</tt> eingegeben. Der natürliche Logarithmus wird als <tt>ln</tt> in Kleinbuchstaben eingegeben. ("Lima November" nicht "India November")';
$string['stackCas_unitssynonym'] = 'Es scheint, dass Sie Einheiten {$a->forbid} haben. Meinten Sie {$a->unit}?';
$string['stackCas_unknownUnitsCase'] = 'Die Eingabe von Einheiten ist groß- und kleinschreibungssensitiv: {$a->forbid} ist eine unbekannte Einheit. Meinten Sie eine aus der folgenden Liste {$a->unit}?';
$string['stackCas_applyingnonobviousfunction'] = 'Dieser Funktionsaufruf {$a->problem} scheint keinen leicht sichtbaren Funktionsnamen zu haben. Aus Sicherheitsgründen müssen Sie den Aufruf möglicherweise vereinfachen, damit der Validator den Funktionsnamen sehen kann.';
$string['stackCas_callingasfunction'] = 'Das Aufrufen des Ergebnisses eines Funktionsaufrufs ist verboten {$a->problem}, Lambdas sind jedoch weiterhin erlaubt.';
$string['stackCas_applyfunmakestring'] = 'Der Name der Funktion kann in <code>{$a->type}</code> kein String sein.';
$string['stackCas_badpostfixop'] = 'Sie haben einen schlechten "Postfix"-Operator in Ihrem Ausdruck.';
$string['stackCas_overrecursivesignatures'] = 'Der Fragecode enthält zu viele Funktionen, die durch Mapping definiert sind';
$string['stackCas_reserved_function'] = 'Der Funktionsname "{$a->name}" ist in dieser Frage nicht erlaubt. Bitte kontaktieren Sie Ihren Lehrer.';
$string['stackCas_studentInputAsFunction'] = 'Die Verwendung von Studenteneingaben als Funktionsname ist nicht erlaubt.';
$string['stackCas_unknownSubstitutionPotenttiallyMaskingAFunctionName'] = 'Der Funktionsname "{$a->name}" ist möglicherweise in unklaren Substitutionen neu definiert.';
$string['stackCas_functionNameSubstitutionToForbiddenOne'] = 'Der Funktionsname "{$a->name}" wird möglicherweise durch Substitutionen auf "{$a->trg}" abgebildet, was verboten ist.';
$string['stackCas_overlyComplexSubstitutionGraphOrRandomisation'] = 'Der Fragecode hat übermäßig komplexe Substitutionen oder baut die Zufälligkeit auf inkrementelle und schwer zu validierende Weise auf, die Validierung hat aus Zeitgründen abgebrochen. Vereinfachen Sie die Logik, um damit umzugehen, und prüfen Sie die Dokumentation auf Anleitungen.';
$string['stackCas_redefine_built_in'] = 'Das Neudefinieren einer integrierten Funktion "{$a->name}" ist verboten.';
$string['stackCas_nested_function_declaration'] = 'Die Definition einer Funktion innerhalb einer anderen Funktion ist jetzt verboten. Verwenden Sie die Umbenennung der Funktion, wenn Sie Funktionsdefinitionen innerhalb einer anderen Funktion wechseln müssen.';
$string['stackCas_decimal_usedthreesep'] = 'Sie haben den Punkt <code>.</code>, das Komma <code>,</code> und das Semikolon <code>;</code> in Ihrem Ausdruck verwendet. Bitte seien Sie konsistent mit Dezimalposition (<code>.</code> oder <code>,</code>) und Listenelementtrennern (<code>,</code> oder <code>;</code>). Ihre Antwort ist mehrdeutig!';
$string['stackCas_decimal_usedcomma'] = 'Sie haben den Punkt <code>.</code> verwendet, aber Sie müssen das Komma <code>,</code> als Dezimaltrennzeichen verwenden!';

// Verwendet in cassession.class.php.
$string['stackCas_CASError'] = 'Das CAS hat die folgenden Fehler zurückgegeben:';
$string['stackCas_allFailed'] = 'Das CAS konnte keine ausgewerteten Ausdrücke zurückgeben. Bitte überprüfen Sie Ihre Verbindung mit dem CAS.';
$string['stackCas_failedReturn'] = 'Das CAS konnte keine Daten zurückgeben.';
$string['stackCas_failedReturnOne'] = 'Das CAS konnte einige Daten nicht zurückgeben.';
$string['stackCas_failedtimeout'] = 'Das CAS konnte aufgrund eines Zeitlimits keine Daten zurückgeben.';

// Verwendet in keyval.class.php.
$string['stackCas_inputsdefined'] = 'Sie dürfen Eingabenamen nicht als Variablen verwenden. Sie haben versucht, <code>{$a}</code> zu definieren';

// Verwendet in castext.class.php.
$string['stackCas_MissingAt'] = 'Es fehlt ein <code>@</code> Zeichen. ';
$string['stackCas_MissingDollar'] = 'Es fehlt ein <code>$</code> Zeichen. ';
$string['stackCas_MissingString'] = 'Es fehlt ein Anführungszeichen <code>"</code>. ';
$string['stackCas_StringOperation'] = 'Ein String scheint am falschen Ort zu sein. Dies ist das Problem: <code>{$a->issue}</code>. ';
$string['stackCas_MissingOpenTeXCAS'] = 'Fehlt <code>{@</code>. ';
$string['stackCas_MissingClosingTeXCAS'] = 'Fehlt <code>@}</code>. ';
$string['stackCas_MissingOpenRawCAS'] = 'Fehlt <code>{#</code>. ';
$string['stackCas_MissingClosingRawCAS'] = 'Fehlt <code>#}</code>. ';
$string['stackCas_MissingOpenDisplay'] = 'Fehlt <code></code>. ';
$string['stackCas_MissingCloseDisplay'] = 'Fehlt <code></code>. ';
$string['stackCas_MissingOpenInline'] = 'Fehlt <code></code>. ';
$string['stackCas_MissingCloseInline'] = 'Fehlt <code></code>. ';
$string['stackCas_MissingOpenHTML'] = 'Fehlt öffnendes HTML-Tag. ';
$string['stackCas_MissingCloseHTML'] = 'Fehlt schließendes HTML-Tag. ';
$string['stackCas_failedValidation'] = 'CASText-Validierung fehlgeschlagen. ';
$string['stackCas_invalidCommand'] = 'CAS-Befehle sind nicht gültig. ';
$string['stackCas_CASErrorCaused'] = 'verursachte den folgenden Fehler:';
$string['stackCas_errorpos'] = 'Etwa in Zeile {$a->line} Zeichen {$a->col}.';

// Verwendet in Blöcken.
$string['stackBlock_ifNeedsCondition'] = 'If-Block benötigt ein Testattribut. ';
$string['stackBlock_escapeNeedsValue'] = 'Escape-Block benötigt ein Wertattribut. ';
$string['stackBlock_unknownBlock'] = 'Der folgende Block ist unbekannt: ';
$string['stackBlock_missmatch'] = 'hat kein passendes Gegenstück. ';
$string['stackBlock_else_out_of_an_if'] = '"else" kann nicht außerhalb eines if-Blocks existieren.';
$string['stackBlock_elif_out_of_an_if'] = '"elif" kann nicht außerhalb eines if-Blocks existieren.';
$string['stackBlock_multiple_else'] = 'Mehrere else-Zweige in einem if-Block.';
$string['stackBlock_elif_after_else'] = '"elif" nach einem "else" in einem if-Block.';
$string['unrecognisedfactstags'] = 'Die folgenden facts-Tag(s) werden nicht erkannt: {$a->tags}.';
$string['stackHintOld'] = 'Der CASText hat alte Hinweis-Tags. Diese sollten jetzt in der Form <pre>[[facts:tag]]</pre> sein';
$string['unknown_block'] = 'Unbekannter Blocktyp {$a->type} angefordert!';

$string['Maxima_DivisionZero'] = 'Division durch Null.';
$string['Maxima_Args'] = 'args: Argument muss ein nicht-atomarer Ausdruck sein. ';
$string['Variable_function'] = 'Die folgenden erscheinen in Ihrem Ausdruck sowohl als Variable als auch als Funktion: {$a->m0}. Bitte klären Sie Ihre Eingabe. Entweder fügen Sie <code></code> Zeichen ein, um Funktionen zu entfernen, oder machen Sie alle Vorkommen zu Funktionen.';
$string['Lowest_Terms'] = 'Ihre Antwort enthält Brüche, die nicht in kleinsten Termen geschrieben sind. Bitte kürzen Sie Faktoren und versuchen Sie es erneut.';
$string['Illegal_floats'] = 'Ihre Antwort enthält Gleitkommazahlen, die hier nicht erlaubt sind. Sie müssen Zahlen als Brüche eingeben. Zum Beispiel sollten Sie 1/3 und nicht 0.3333 eingeben, was schließlich nur eine Annäherung an ein Drittel ist.';
$string['Illegal_strings'] = 'Ihre Antwort enthält "Strings", die hier nicht erlaubt sind.';
$string['Illegal_lists'] = 'Ihre Antwort enthält Listen "[a,b,c]", die hier nicht erlaubt sind.';
$string['Illegal_sets'] = 'Ihre Antwort enthält Mengen "{a,b,c}", die hier nicht erlaubt sind.';
$string['Illegal_groups'] = 'Ihre Antwort enthält Bewertungsgruppen "(a,b,c)", die hier nicht erlaubt sind.';
$string['Illegal_groupping'] = 'Ihre Antwort enthält Klammern, die zur Gruppierung von Operationen verwendet werden, diese sind hier verboten. Sie sollten wahrscheinlich den Ausdruck manipulieren, um sie zu beseitigen.';
$string['Illegal_control_flow'] = 'Ihre Antwort enthält Kontrollflussanweisungen wie die <code>if</code>-Bedingung oder die <code>do</code>-Schleife, diese sind hier verboten. Sie sollten wahrscheinlich das Ergebnis dieser Anweisungen als Antwort angeben.';
$string['qm_error'] = 'Ihre Antwort enthält Fragezeichen ?, die in Antworten nicht erlaubt sind. Sie sollten diese durch einen bestimmten Wert ersetzen.';
$string['Equiv_Illegal_set'] = 'Mengen sind beim Schlussfolgern durch Äquivalenz nicht erlaubt.';
$string['Equiv_Illegal_list'] = 'Listen sind beim Schlussfolgern durch Äquivalenz nicht erlaubt.';
$string['Equiv_Illegal_matrix'] = 'Matrizen sind beim Schlussfolgern durch Äquivalenz nicht erlaubt.';
$string['CommaError'] = 'Ihre Antwort enthält Kommata, die nicht Teil einer Liste, Menge oder Matrix sind. <ul><li>Wenn Sie eine Liste eingeben wollten, verwenden Sie bitte <tt>{...}</tt>,</li><li>Wenn Sie eine Menge eingeben wollten, verwenden Sie bitte <tt>{...}</tt>.</li></ul>';
$string['Bad_assignment'] = 'Beim Auflisten der Werte einer Variablen sollten Sie dies auf folgende Weise tun: {$a->m0}. Bitte ändern Sie Ihre Eingabe.';
$string['ValidateVarsSpurious'] = 'Diese Variablen werden nicht benötigt: {$a->m0}.';
$string['ValidateVarsMissing'] = 'Diese Variablen fehlen: {$a->m0}.';
$string['Illegal_identifiers_in_units'] = 'Die Eingabe enthält einen Variablennamen, wenn nur Einheiten erwartet wurden.';
$string['Illegal_illegal_operation_in_units'] = 'Der Operator <code>{$a}</code> ist in dieser Eingabe nicht erlaubt.';
$string['Illegal_illegal_power_of_ten_in_units'] = 'Der Wert darf keine nicht ganzzahligen Zehnerpotenzen enthalten.';
$string['Illegal_input_form_units'] = 'Diese Eingabe erwartet einen numerischen Wert, gefolgt oder multipliziert mit einem Ausdruck, der eine Einheit definiert, z.B. <code>1.23W/m^2</code>. Beachten Sie, dass die hier erforderliche Einheit etwas anderes sein kann.';
$string['Illegal_x10'] = 'Ihre Antwort scheint das Zeichen "x" als Multiplikationszeichen zu verwenden. Bitte verwenden Sie <code>*</code> für die Multiplikation.';

$string['stackBlock_jsxgraph_width'] = 'Die Breite eines JSXGraph muss eine bekannte CSS-Längeneinheit verwenden.';
$string['stackBlock_jsxgraph_height'] = 'Die Höhe eines JSXGraph muss eine bekannte CSS-Längeneinheit verwenden.';
$string['stackBlock_jsxgraph_width_num'] = 'Der numerische Teil der Breite eines JSXGraph muss eine reine Zahl sein und darf keine zusätzlichen Zeichen enthalten.';
$string['stackBlock_jsxgraph_height_num'] = 'Der numerische Teil der Höhe eines JSXGraph muss eine reine Zahl sein und darf keine zusätzlichen Zeichen enthalten.';
$string['stackBlock_jsxgraph_underdefined_dimension'] = 'Bei der Definition des Seitenverhältnisses für das JSXGraph muss entweder die Breite oder die Höhe des Graphen definiert werden.';
$string['stackBlock_jsxgraph_overdefined_dimension'] = 'Bei der Definition des Seitenverhältnisses für das JSXGraph sollte nur Breite oder Höhe definiert werden, nicht beides.';
$string['stackBlock_jsxgraph_ref'] = 'Der jsxgraph-Block unterstützt nur Referenzen auf Eingaben, die im selben CASText-Abschnitt vorhanden sind. \'{$a->var}\' existiert hier nicht.';
$string['stackBlock_jsxgraph_param'] = 'Der jsxgraph-Block unterstützt nur diese Parameter in diesem Kontext: {$a->param}.';

$string['stackBlock_parsons_used_header'] = 'Konstruieren Sie hier Ihre Lösung:';
$string['stackBlock_parsons_available_header'] = 'Ziehen Sie von hier:';
$string['stackBlock_parsons_width'] = 'Die Breite eines Parson-Blocks muss eine bekannte CSS-Längeneinheit verwenden.';
$string['stackBlock_parsons_height'] = 'Die Höhe eines Parson-Blocks muss eine bekannte CSS-Längeneinheit verwenden.';
$string['stackBlock_parsons_width_num'] = 'Der numerische Teil der Breite eines Parson-Blocks muss eine reine Zahl sein und darf keine zusätzlichen Zeichen enthalten.';
$string['stackBlock_parsons_height_num'] = 'Der numerische Teil der Höhe eines Parson-Blocks muss eine reine Zahl sein und darf keine zusätzlichen Zeichen enthalten.';
$string['stackBlock_parsons_length_num'] = 'Der numerische Wert der Länge muss eine positive ganze Zahl sein und darf keine zusätzlichen Zeichen oder numerischen Typen enthalten.';
$string['stackBlock_parsons_underdefined_dimension'] = 'Bei der Definition des Seitenverhältnisses für einen Parson-Block muss entweder die Breite oder die Höhe der Listen definiert werden.';
$string['stackBlock_parsons_overdefined_dimension'] = 'Bei der Definition des Seitenverhältnisses für einen Parson-Block sollte nur Breite oder Höhe definiert werden, nicht beides.';
$string['stackBlock_parsons_unknown_named_version'] = 'Der Parson-Block unterstützt nur Versionen mit den Namen: {$a->version}.';
$string['stackBlock_parsons_unknown_mathjax_version'] = 'Der Parson-Block unterstützt nur MathJax-Versionen {$a->mjversion} für den mathjax-Parameter.';
$string['stackBlock_parsons_ref'] = 'Der Parson-Block unterstützt nur Referenzen auf Eingaben, die im selben CASText-Abschnitt vorhanden sind. \'{$a->var}\' existiert hier nicht.';
$string['stackBlock_parsons_param'] = 'Der Parson-Block unterstützt nur diese Parameter in diesem Kontext: \'{$a->var}\'.';
$string['stackBlock_parsons_contents'] = 'Der Inhalt eines Parson-Blocks muss ein JSON in der Form {#stackjson_stringify(proof_steps)#} sein. Wenn Sie benutzerdefinierte Objekte übergeben, sollte der Inhalt des Parson-Blocks ein JSON in der Form {steps: {#stackjson_stringify(proof_steps)#}, options: {JSON mit Sortable-Optionen}} sein. Alternativ können die Inhalte des Parson-Blocks rohe JSON-Äquivalente enthalten. Stellen Sie sicher, dass die Maxima-Variable proof_steps das korrekte Format hat. Beachten Sie, dass alle Beweisschritte Strings sein müssen. Details finden Sie in der Dokumentation.';
$string['stackBlock_unknown_sortable_option'] = 'Unbekannte Sortable-Optionen gefunden, die folgenden werden ignoriert: ';
$string['stackBlock_overwritten_sortable_option'] = 'Unveränderbare Sortable-Optionen gefunden, die folgenden werden ignoriert: ';

// Definition der stackBlock GeoGebra Strings.
$string['stackBlock_geogebra_width'] = 'Die Breite eines GeoGebra-Applets muss eine bekannte CSS-Längeneinheit verwenden.';
$string['stackBlock_geogebra_height'] = 'Die Höhe eines GeoGebra-Applets muss eine bekannte CSS-Längeneinheit verwenden.';
$string['stackBlock_geogebra_width_num'] = 'Der numerische Teil der Breite eines GeoGebra-Applets muss eine reine Zahl sein und darf keine zusätzlichen Zeichen enthalten.';
$string['stackBlock_geogebra_height_num'] = 'Der numerische Teil der Höhe eines GeoGebra-Applets muss eine reine Zahl sein und darf keine zusätzlichen Zeichen enthalten.';
$string['stackBlock_geogebra_underdefined_dimension'] = 'Bei der Definition des Seitenverhältnisses für das GeoGebra-Applet muss entweder die Breite oder die Höhe des Graphen definiert werden.';
$string['stackBlock_geogebra_overdefined_dimension'] = 'Bei der Definition des Seitenverhältnisses für das GeoGebra-Applet sollte nur Breite oder Höhe definiert werden, nicht beides.';
$string['stackBlock_geogebra_ref'] = 'Der geogebra-Block unterstützt nur Referenzen auf Eingaben, die im selben CASText-Abschnitt vorhanden sind. \'{$a->var}\' existiert hier nicht.';
$string['stackBlock_geogebra_param'] = 'Der geogebra-Block unterstützt nur diese Parameter in diesem Kontext: {$a->param}.';
$string['stackBlock_geogebra_link'] = 'Verlinkung zu referenziertem GeoGebra-Material';
$string['stackBlock_geogebra_link_help'] = 'Möchten Sie dieses Material bearbeiten? Wenn es sich um Ihr eigenes GeoGebra-Material auf geogebra.org handelt, können Sie es bearbeiten. Wenn dies nicht Ihr GeoGebra-Material ist, müssen Sie das Material zuerst auf geogebra.org kopieren. Dann müssen Sie das Material veröffentlichen und den Wert material_id unten im Fragetext bearbeiten.';
$string['stackBlock_geogebra_heading'] = 'GeoGebra-Materialien';
// Definition der stackBlock GeoGebra Strings für globale Admin-Optionen.
$string['stackBlock_geogebra_settingdefaultoptions'] = 'Optionen für GeoGebra in STACK';
$string['stackBlock_geogebra_settingdefaultoptions_desc'] = 'Die Dokumentation zur Verwendung von GeoGebra mit STACK finden Sie unter Authoring/GeoGebra.md';
$string['stackBlock_geogebrabaseurl'] = 'Link zum GeoGebra-Hosting (optional)';
$string['stackBlock_geogebrabaseurl_help'] = 'Hier können Sie einen benutzerdefinierten Link hinzufügen, wenn Sie GeoGebra-Skripte auf Ihrem eigenen Server hosten. Wenn Sie nur eine bestimmte GeoGebra-Version verwenden möchten, verwenden Sie: https://www.geogebra.org/apps/5.0.498.0/web3d (z.B. für Version 5.0.498.0)';

// Answer tests.
$string['stackOptions_AnsTest_values_AlgEquiv']            = "AlgEquiv";
$string['stackOptions_AnsTest_values_AlgEquivNouns']       = "AlgEquivNouns";
$string['stackOptions_AnsTest_values_EqualComAss']         = "EqualComAss";
$string['stackOptions_AnsTest_values_EqualComAssRules']    = "EqualComAssRules";
$string['stackOptions_AnsTest_values_CasEqual']            = "CasEqual";
$string['stackOptions_AnsTest_values_SameType']            = "SameType";
$string['stackOptions_AnsTest_values_SubstEquiv']          = "SubstEquiv";
$string['stackOptions_AnsTest_values_SysEquiv']            = "SysEquiv";
$string['stackOptions_AnsTest_values_Sets']                = "Sets";
$string['stackOptions_AnsTest_values_Expanded']            = "Expanded";
$string['stackOptions_AnsTest_values_FacForm']             = "FacForm";
$string['stackOptions_AnsTest_values_SingleFrac']          = "SingleFrac";
$string['stackOptions_AnsTest_values_PartFrac']            = "PartFrac";
$string['stackOptions_AnsTest_values_CompSquare']          = "CompletedSquare";
$string['stackOptions_AnsTest_values_PropLogic']           = "PropositionalLogic";
$string['stackOptions_AnsTest_values_Equiv']               = "EquivReasoning";
$string['stackOptions_AnsTest_values_EquivFirst']          = "EquivFirst";
$string['stackOptions_AnsTest_values_SigFigsStrict']       = "SigFigsStrict";
$string['stackOptions_AnsTest_values_NumRelative']         = "NumRelative";
$string['stackOptions_AnsTest_values_NumAbsolute']         = "NumAbsolute";
$string['stackOptions_AnsTest_values_NumSigFigs']          = "NumSigFigs";
$string['stackOptions_AnsTest_values_NumDecPlaces']        = "NumDecPlaces";
$string['stackOptions_AnsTest_values_NumDecPlacesWrong']   = "NumDecPlacesWrong";
$string['stackOptions_AnsTest_values_UnitsSigFigs']        = "UnitsSigFigs";
$string['stackOptions_AnsTest_values_UnitsStrictSigFigs']  = "UnitsStrictSigFigs";
$string['stackOptions_AnsTest_values_UnitsRelative']       = "UnitsRelative";
$string['stackOptions_AnsTest_values_UnitsStrictRelative'] = "UnitsStrictRelative";
$string['stackOptions_AnsTest_values_UnitsAbsolute']       = "UnitsAbsolute";
$string['stackOptions_AnsTest_values_UnitsStrictAbsolute'] = "UnitsStrictAbsolute";
$string['stackOptions_AnsTest_values_GT']                  = "Num-GT";
$string['stackOptions_AnsTest_values_GTE']                 = "Num-GTE";
$string['stackOptions_AnsTest_values_LowestTerms']         = "LowestTerms";
$string['stackOptions_AnsTest_values_Diff']                = "Diff";
$string['stackOptions_AnsTest_values_Int']                 = "Int";
$string['stackOptions_AnsTest_values_String']              = "String";
$string['stackOptions_AnsTest_values_StringSloppy']        = "StringSloppy";
$string['stackOptions_AnsTest_values_Levenshtein']         = "Levenshtein";
$string['stackOptions_AnsTest_values_SRegExp']             = "SRegExp";

$string['AT_NOTIMPLEMENTED'] = 'Dieser Antworttest wurde noch nicht implementiert. ';
$string['TEST_FAILED'] = 'Der Antworttest konnte nicht korrekt ausgeführt werden: Bitte informieren Sie Ihren Lehrer. {$a->errors}';
$string['TEST_FAILED_Q'] = 'Der Antworttest konnte nicht korrekt ausgeführt werden: Bitte informieren Sie Ihren Lehrer. ';
$string['AT_MissingOptions'] = 'Fehlende Option bei der Ausführung des Tests. ';
$string['AT_InvalidOptions'] = 'Optionsfeld ist ungültig. {$a->errors}';
$string['AT_EmptySA'] = 'Es wurde versucht, einen Antworttest mit einer leeren Schülerantwort auszuführen, wahrscheinlich ein CAS-Validierungsproblem bei der Erstellung der Frage.';
$string['AT_EmptyTA'] = 'Es wurde versucht, einen Antworttest mit einer leeren Lehrerantwort auszuführen, wahrscheinlich ein CAS-Validierungsproblem bei der Erstellung der Frage.';
$string['AT_raw_sans_needed'] = 'Einige Antworttests basieren auf der rohen Eingabe eines Schülers, daher sollte das Feld "SAns" des Knotens der Name einer Frageeingabe sein. Bitte überprüfen Sie das Folgende (prt.node), das eher wie ein berechneter Wert aussieht: {$a->prt}';

$string['ATString_SA_not_string'] = 'Ihre Antwort sollte ein String sein, ist es aber nicht. ';
$string['ATString_SB_not_string'] = 'Die Antwort des Lehrers sollte ein String sein, ist es aber nicht. ';

$string['ATAlgEquiv_SA_not_expression'] = 'Ihre Antwort sollte ein Ausdruck sein, keine Gleichung, Ungleichung, Liste, Menge oder Matrix. ';
$string['ATAlgEquiv_SA_not_matrix'] = 'Ihre Antwort sollte eine Matrix sein, ist es aber nicht. ';
$string['ATAlgEquiv_SA_not_list'] = 'Ihre Antwort sollte eine Liste sein, ist es aber nicht. Beachten Sie, dass die Syntax zum Eingeben einer Liste darin besteht, die kommagetrennten Werte in eckigen Klammern einzuschließen. ';
$string['ATAlgEquiv_SA_not_set'] = 'Ihre Antwort sollte eine Menge sein, ist es aber nicht. Beachten Sie, dass die Syntax zum Eingeben einer Menge darin besteht, die kommagetrennten Werte in geschweiften Klammern einzuschließen. ';
$string['ATAlgEquiv_SA_not_realset'] = 'Ihre Antwort sollte eine Teilmenge der reellen Zahlen sein. Das könnte eine Menge von Zahlen oder eine Sammlung von Intervallen sein.';
$string['ATAlgEquiv_SA_not_equation'] = 'Ihre Antwort sollte eine Gleichung sein, ist es aber nicht. ';
$string['ATAlgEquiv_SA_not_logic'] = 'Ihre Antwort sollte eine Gleichung, Ungleichung oder eine logische Kombination aus vielen dieser sein, ist es aber nicht. ';
$string['ATAlgEquiv_TA_not_equation'] = 'Sie haben eine Gleichung eingegeben, aber hier wird keine Gleichung erwartet. Sie haben möglicherweise etwas wie "y=2x+1" eingegeben, als Sie nur "2x+1" hätten eingeben müssen. ';
$string['ATAlgEquiv_SA_not_inequality'] = 'Ihre Antwort sollte eine Ungleichung sein, ist es aber nicht. ';
$string['ATAlgEquiv_SA_not_function'] = 'Ihre Antwort sollte eine Funktion sein, definiert mit dem Operator <tt>:=</tt>, ist es aber nicht. ';
$string['ATAlgEquiv_SA_not_string'] = 'Ihre Antwort sollte ein String sein, ist es aber nicht. ';
$string['Subst'] = 'Ihre Antwort wäre korrekt, wenn Sie die folgende Substitution von Variablen verwenden würden. {$a->m0} ';

$string['ATSubstEquiv_Opt_List'] = 'Die Option für diesen Antworttest muss eine Liste sein. Das ist ein Fehler. Bitte informieren Sie Ihren Lehrer. ';

$string['ATEqualComAssRules_Opt_List'] = 'Die Option für diesen Antworttest muss eine nicht leere Liste unterstützter Regeln sein. Das ist ein Fehler. Bitte informieren Sie Ihren Lehrer. ';
$string['ATEqualComAssRules_Opt_Incompatible'] = 'Die Option für diesen Antworttest enthält unvereinbare Regeln. Das ist ein Fehler. Bitte informieren Sie Ihren Lehrer. ';

$string['ATSets_SA_not_set'] = 'Ihre Antwort sollte eine Menge sein, ist es aber nicht. Beachten Sie, dass die Syntax zum Eingeben einer Menge darin besteht, die kommagetrennten Werte in geschweiften Klammern einzuschließen. ';
$string['ATSets_SB_not_set'] = 'Der "Sets" Antworttest erwartet, dass sein zweites Argument eine Menge ist. Das ist ein Fehler. Bitte informieren Sie Ihren Lehrer.';
$string['ATSets_wrongentries'] = 'Diese Einträge sollten nicht Elemente Ihrer Menge sein. {$a->m0} ';
$string['ATSets_missingentries'] = 'Die folgenden fehlen in Ihrer Menge. {$a->m0} ';
$string['ATSets_duplicates'] = 'Ihre Menge scheint doppelte Einträge zu enthalten!';

$string['ATInequality_nonstrict'] = 'Ihre Ungleichung sollte streng sein, ist es aber nicht! ';
$string['ATInequality_strict'] = 'Ihre Ungleichung sollte nicht streng sein! ';
$string['ATInequality_backwards'] = 'Ihre Ungleichung scheint rückwärts zu sein. ';

$string['ATLowestTerms_wrong'] = 'Sie müssen Brüche in Ihrer Antwort kürzen. ';
$string['ATLowestTerms_entries'] = 'Die folgenden Terme in Ihrer Antwort sind nicht in kleinsten Termen. {$a->m0} Bitte versuchen Sie es erneut. ';
$string['ATLowestTerms_not_rat'] = 'Sie müssen das Folgende aus dem Nenner Ihres Bruchs entfernen: {$a->m0}';

$string['ATList_wronglen'] = 'Ihre Liste sollte {$a->m0} Elemente haben, hat aber tatsächlich {$a->m1}. ';
$string['ATList_wrongentries'] = 'Die unten rot unterstrichenen Einträge sind diejenigen, die falsch sind. {$a->m0} ';

$string['ATMatrix_wrongsz'] = 'Ihre Matrix sollte {$a->m0} mal {$a->m1} sein, ist aber tatsächlich {$a->m2} mal {$a->m3}. ';
$string['ATMatrix_wrongentries'] = 'Die unten rot unterstrichenen Einträge sind diejenigen, die falsch sind. {$a->m0} ';

$string['ATSet_wrongsz'] = 'Ihre Menge sollte {$a->m0} verschiedene Elemente haben, hat aber tatsächlich {$a->m1}. ';
$string['ATSet_wrongentries'] = 'Die folgenden Einträge sind falsch, obwohl sie möglicherweise in einer vereinfachten Form von dem erscheinen, was Sie tatsächlich eingegeben haben. {$a->m0} ';

$string['irred_Q_factored'] = 'Der Term {$a->m0} sollte nicht faktorisiert sein, ist es aber. ';
$string['irred_Q_commonint'] = 'Sie müssen einen gemeinsamen Faktor herausnehmen. '; // Braucht ein Leerzeichen am Ende.
$string['irred_Q_optional_fac'] = 'Sie könnten noch mehr Arbeit leisten, da {$a->m0} weiter faktorisiert werden kann. Sie müssen jedoch nicht. ';

$string['FacForm_UnPick_morework'] = 'Sie könnten noch etwas mehr Arbeit in den Term {$a->m0} stecken. ';
$string['FacForm_UnPick_intfac'] = 'Sie müssen einen gemeinsamen Faktor herausnehmen. ';

$string['ATFacForm_error_list'] = 'Der Antworttest ist fehlgeschlagen. Bitte kontaktieren Sie Ihren Systemadministrator';
$string['ATFacForm_isfactored'] = 'Ihre Antwort ist faktorisiert, gut gemacht. '; // Braucht ein Leerzeichen am Ende.
$string['ATFacForm_notfactored'] = 'Ihre Antwort ist nicht faktorisiert. '; // Braucht ein Leerzeichen am Ende.
$string['ATFacForm_notpoly'] = 'Dieser Term sollte ein Polynom sein, ist es aber nicht.';
$string['ATFacForm_notalgequiv'] = 'Beachten Sie, dass Ihre Antwort nicht algebraisch äquivalent zur korrekten Antwort ist. Sie müssen etwas falsch gemacht haben. '; // Braucht ein Leerzeichen am Ende.

$string['ATPartFrac_error_list'] = 'Der Antworttest ist fehlgeschlagen. Bitte kontaktieren Sie Ihren Systemadministrator';
$string['ATPartFrac_true'] = '';
$string['ATPartFrac_single_fraction'] = 'Ihre Antwort scheint eine einzelne Fraktion zu sein, sie muss in einer Form mit Teilbrüchen sein. ';
$string['ATPartFrac_diff_variables'] = 'Die Variablen in Ihrer Antwort unterscheiden sich von denen der Frage, bitte überprüfen Sie sie. ';
$string['ATPartFrac_denom_ret'] = 'Wenn Ihre Antwort als einzelne Fraktion geschrieben wäre, dann wäre der Nenner {$a->m0}. Tatsächlich sollte er {$a->m1} sein. ';
$string['ATPartFrac_ret_expression'] = 'Ihre Antwort als einzelne Fraktion ist {$a->m0} ';

$string['ATSingleFrac_error_list'] = 'Der Antworttest ist fehlgeschlagen. Bitte kontaktieren Sie Ihren Systemadministrator';
$string['ATSingleFrac_true'] = '';
$string['ATCompSquare_false_no_summands']  = 'The completed square is of the form \( a(\cdots\cdots)^2 + b\) where \(a\) and \(b\) do not depend on your variable.  More than one of your summands appears to depend on the variable in your answer.';
$string['ATSingleFrac_var'] = 'Die Variablen in Ihrer Antwort unterscheiden sich von denen der Frage, bitte überprüfen Sie sie. ';
$string['ATSingleFrac_ret_exp'] = 'Ihre Antwort ist nicht algebraisch äquivalent zur korrekten Antwort. Sie müssen etwas falsch gemacht haben. ';
$string['ATSingleFrac_div'] = 'Ihre Antwort enthält Fraktionen innerhalb von Fraktionen. Sie müssen diese beseitigen und Ihre Antwort als einzelne Fraktion schreiben.';

$string['ATCompSquare_true'] = '';
$string['ATCompSquare_false'] = '';
$string['ATCompSquare_not_AlgEquiv'] = 'Ihre Antwort scheint in der richtigen Form zu sein, ist aber nicht äquivalent zur korrekten Antwort.';
$string['ATCompSquare_false_no_summands']  = 'The completed square is of the form \( a(\cdots\cdots)^2 + b\) where \(a\) and \(b\) do not depend on your variable.  More than one of your summands appears to depend on the variable in your answer.';
$string['ATCompSquare_SA_not_depend_var'] = 'Ihre Antwort sollte von der Variablen {$a->m0} abhängen, tut es aber nicht!';

$string['ATInt_error_list'] = 'Der Antworttest ist fehlgeschlagen. Bitte kontaktieren Sie Ihren Systemadministrator';
$string['ATInt_const_int'] = 'Sie müssen eine Integrationskonstante hinzufügen. Dies sollte eine willkürliche Konstante sein, keine Zahl.';
$string['ATInt_const'] = 'Sie müssen eine Integrationskonstante hinzufügen, ansonsten scheint dies korrekt zu sein. Gut gemacht.';
$string['ATInt_EqFormalDiff'] = 'Die formale Ableitung Ihrer Antwort entspricht dem Ausdruck, den Sie integrieren sollten. Ihre Antwort unterscheidet sich jedoch signifikant von der korrekten Antwort, also nicht nur z. B. eine Integrationskonstante. Bitte fragen Sie Ihren Lehrer dazu.';
$string['ATInt_logabs_inconsistent'] = 'There appear to be strange inconsistencies between your use of \(\log(...)\) and \(\log(|...|)\).  Please ask your teacher about this.  ';
$string['ATInt_weirdconst'] = 'Die formale Ableitung Ihrer Antwort entspricht dem Ausdruck, den Sie integrieren sollten. Sie haben jedoch eine seltsame Integrationskonstante. Bitte fragen Sie Ihren Lehrer dazu.';
$string['ATInt_logabs']              = 'Your teacher may expect you to use the result \(\int\frac{1}{x} dx = \log(|x|)+c\), rather than \(\int\frac{1}{x} dx = \log(x)+c\).  Please ask your teacher about this.';
$string['ATInt_diff'] = 'Es scheint, als hätten Sie stattdessen differenziert!';
$string['ATInt_generic'] = 'Die Ableitung Ihrer Antwort sollte gleich dem Ausdruck sein, den Sie integrieren sollten, das war: {$a->m0} Tatsächlich ist die Ableitung Ihrer Antwort, bezogen auf {$a->m1}, {$a->m2}, also müssen Sie etwas falsch gemacht haben!';
$string['ATInt_STACKERROR_OptList'] = 'Der Antworttest konnte nicht korrekt ausgeführt werden: Bitte informieren Sie Ihren Lehrer. Wenn die Option zu ATInt eine Liste ist, muss sie genau zwei Elemente haben, hat sie aber nicht.';

$string['ATDiff_error_list'] = 'Der Antworttest ist fehlgeschlagen. Bitte kontaktieren Sie Ihren Systemadministrator';
$string['ATDiff_int'] = 'Es scheint, als hätten Sie stattdessen integriert!';

$string['ATNumerical_SA_not_list'] = 'Ihre Antwort sollte eine Liste sein, ist es aber nicht. Beachten Sie, dass die Syntax zum Eingeben einer Liste darin besteht, die kommagetrennten Werte in eckigen Klammern einzuschließen. ';
$string['ATNumerical_SA_not_set'] = 'Ihre Antwort sollte eine Menge sein, ist es aber nicht. Beachten Sie, dass die Syntax zum Eingeben einer Menge darin besteht, die kommagetrennten Werte in geschweiften Klammern einzuschließen. ';
$string['ATNumerical_SA_not_number'] = 'Ihre Antwort sollte eine Gleitkommazahl sein, ist es aber nicht. ';
$string['ATNumerical_SB_not_number'] = 'Der Wert, der für die Antwort des Lehrers angegeben wurde, sollte eine Gleitkommazahl sein, ist es aber nicht. Dies ist ein interner Fehler beim Test. Bitte fragen Sie Ihren Lehrer dazu.';
$string['ATNumerical_FAILED'] = 'Ihre Antwort sollte eine Gleitkommazahl, oder eine Liste oder Menge von Zahlen sein. Das ist sie nicht. ';
$string['ATNumerical_STACKERROR_tol'] = 'Die numerische Toleranz für ATNumerical sollte eine Gleitkommazahl sein, ist es aber nicht. Dies ist ein interner Fehler beim Test. Bitte fragen Sie Ihren Lehrer dazu. ';

$string['ATNum_OutofRange'] = 'Ein numerischer Ausdruck liegt außerhalb des unterstützten Bereichs. Bitte kontaktieren Sie Ihren Lehrer. ';

$string['ATNumSigFigs_error_list'] = 'Der Antworttest ist fehlgeschlagen. Bitte kontaktieren Sie Ihren Systemadministrator';
$string['ATNumSigFigs_NotDecimal'] = 'Ihre Antwort sollte eine Dezimalzahl sein, ist es aber nicht! ';
$string['ATNumSigFigs_WrongSign'] = 'Ihre Antwort hat das falsche algebraische Vorzeichen. ';
$string['ATNumSigFigs_Inaccurate'] = 'Die Genauigkeit Ihrer Antwort ist nicht korrekt. Entweder haben Sie nicht richtig gerundet, oder Sie haben eine Zwischenantwort gerundet, was zu einem Fehler führt.';
$string['ATNumSigFigs_WrongDigits'] = 'Ihre Antwort enthält die falsche Anzahl an signifikanten Ziffern. ';

$string['ATUnits_SA_not_expression'] = 'Ihre Antwort muss eine Zahl zusammen mit Einheiten sein. Verwenden Sie keine Mengen, Listen, Gleichungen oder Matrizen. ';
$string['ATUnits_SA_no_units'] = 'Ihre Antwort muss Einheiten enthalten. ';
$string['ATUnits_SA_excess_units'] = 'Ihre Antwort hat Einheiten (oder Variablen) verwendet, sollte es aber nicht. ';
$string['ATUnits_SA_only_units'] = 'Ihre Antwort muss eine Zahl zusammen mit Einheiten sein. Ihre Antwort hat nur Einheiten. ';
$string['ATUnits_SA_bad_units'] = 'Ihre Antwort muss Einheiten enthalten, und Sie müssen Multiplikation verwenden, um die Einheiten an einen Wert anzuhängen, z.B. <code>3.2*m/s</code>. ';
$string['ATUnits_SA_errorbounds_invalid'] = 'Ihre Antwort hat Fehlergrenzen. In diesem Fall sollten Sie keine Fehlergrenzen angeben, sondern nur die Menge und Einheiten verwenden. ';
$string['ATUnits_SO_wrong_units'] = 'Die Einheiten, die für die numerische Toleranz angegeben wurden, müssen mit den Einheiten der Antwort des Lehrers übereinstimmen. Dies ist ein interner Fehler beim Test. Bitte fragen Sie Ihren Lehrer dazu.';
$string['ATUnits_incompatible_units'] = 'Ihre Einheiten sind nicht kompatibel mit denen, die der Lehrer verwendet hat. ';
$string['ATUnits_compatible_units'] = 'Ihre Einheiten unterscheiden sich von denen des Lehrers, sind aber mit ihnen kompatibel. Numerische Werte werden für den Vergleich in SI-Basiseinheiten umgerechnet. ';
$string['ATUnits_correct_numerical'] = 'Bitte überprüfen Sie Ihre Einheiten sorgfältig. ';

$string['ATNumDecPlaces_OptNotInt'] = 'Für ATNumDecPlaces muss die Testoption eine positive ganze Zahl sein, tatsächlich wurde "{$a->m0}" erhalten. ';
$string['ATNumDecPlaces_NoDP'] = 'Ihre Antwort muss eine Dezimalzahl sein, einschließlich eines Dezimalpunkts. ';
$string['ATNumDecPlaces_Wrong_DPs'] = 'Ihre Antwort wurde mit der falschen Anzahl von Dezimalstellen angegeben.';
$string['ATNumDecPlaces_Float'] = 'Ihre Antwort muss eine Gleitkommazahl sein, ist es aber nicht.';

$string['ATNumDecPlacesWrong_OptNotInt'] = 'Für ATNumDecPlacesWrong muss die Testoption eine positive ganze Zahl sein, tatsächlich wurde "{$a->m0}" erhalten. ';

$string['ATSysEquiv_SA_not_list'] = 'Ihre Antwort sollte eine Liste sein, ist es aber nicht!';
$string['ATSysEquiv_SB_not_list'] = 'Die Antwort des Lehrers ist keine Liste. Bitte kontaktieren Sie Ihren Lehrer.';
$string['ATSysEquiv_SA_not_eq_list'] = 'Ihre Antwort sollte eine Liste von Gleichungen sein, ist es aber nicht!';
$string['ATSysEquiv_SB_not_eq_list'] = 'Die Antwort des Lehrers ist keine Liste von Gleichungen, sollte es aber sein.';
$string['ATSysEquiv_SA_not_poly_eq_list'] = 'Eine oder mehrere Ihrer Gleichungen sind keine Polynome!';
$string['ATSysEquiv_SB_not_poly_eq_list'] = 'Die Antwort des Lehrers sollte eine Liste von Polynomgleichungen sein, ist es aber nicht. Bitte kontaktieren Sie Ihren Lehrer.';
$string['ATSysEquiv_SA_missing_variables'] = 'Ihrer Antwort fehlen eine oder mehrere Variablen!';
$string['ATSysEquiv_SA_extra_variables'] = 'Ihre Antwort enthält zu viele Variablen!';
$string['ATSysEquiv_SA_wrong_variables'] = 'Ihre Antwort verwendet die falschen Variablen!';
$string['ATSysEquiv_SA_system_underdetermined'] = 'Die Gleichungen in Ihrem System scheinen korrekt zu sein, aber Sie benötigen weitere.';
$string['ATSysEquiv_SA_system_overdetermined'] = 'Die unten rot unterstrichenen Einträge sind diejenigen, die falsch sind. {$a->m0} ';

$string['ATLevenshtein_SA_not_string'] = 'Das erste Argument des Levenshtein-Antworttests muss ein String sein. Der Test ist fehlgeschlagen. Bitte kontaktieren Sie Ihren Lehrer.';
$string['ATLevenshtein_SB_malformed'] = 'Das zweite Argument des Levenshtein-Antworttests muss in der Form [allow, deny] sein, wobei jedes Element eine Liste von Strings ist. Dieses Argument ist fehlerhaft und der Test ist fehlgeschlagen. Bitte kontaktieren Sie Ihren Lehrer.';
$string['ATLevenshtein_tol_not_number'] = 'Die Toleranz im Levenshtein-Antworttest muss eine Zahl sein, ist es aber nicht. Der Test ist fehlgeschlagen. Bitte kontaktieren Sie Ihren Lehrer.';
$string['ATLevenshtein_upper_not_boolean'] = 'Die Option zur Groß-/Kleinschreibung im Levenshtein-Antworttest muss ein Boolescher Wert sein, ist es aber nicht. Der Test ist fehlgeschlagen. Bitte kontaktieren Sie Ihren Lehrer.';
$string['ATLevenshtein_match'] = 'Die nächstliegende Übereinstimmung war "{$a->m0}".';

$string['ATSRegExp_SB_not_string'] = 'Das zweite Argument des SRegExp-Antworttests muss ein String sein. Der Test ist fehlgeschlagen. Bitte kontaktieren Sie Ihren Lehrer.';
$string['ATSRegExp_SA_not_string'] = 'Das erste Argument des SRegExp-Antworttests muss ein String sein. Der Test ist fehlgeschlagen. Bitte kontaktieren Sie Ihren Lehrer.';

$string['ATEquiv_SA_not_list'] = 'Das erste Argument des Equiv-Antworttests sollte eine Liste sein, aber der Test ist fehlgeschlagen. Bitte kontaktieren Sie Ihren Lehrer.';
$string['ATEquiv_SB_not_list'] = 'Das zweite Argument des Equiv-Antworttests sollte eine Liste sein, aber der Test ist fehlgeschlagen. Bitte kontaktieren Sie Ihren Lehrer.';
$string['ATEquivFirst_SA_not_list'] = 'Das erste Argument des Equiv-Antworttests sollte eine Liste sein, aber der Test ist fehlgeschlagen. Bitte kontaktieren Sie Ihren Lehrer.';
$string['ATEquivFirst_SB_not_list'] = 'Das zweite Argument des Equiv-Antworttests sollte eine Liste sein, aber der Test ist fehlgeschlagen. Bitte kontaktieren Sie Ihren Lehrer.';
$string['ATEquivFirst_SA_wrong_start'] = 'Die erste Zeile Ihres Arguments muss "{$a->m0}" sein.';
$string['ATEquivFirst_SA_wrong_end'] = 'Ihre endgültige Antwort ist nicht in der korrekten Form. ';
$string['equiv_SAMEROOTS'] = '(Gleiche Wurzeln)';
$string['equiv_ANDOR'] = 'Verwechslung von "und/oder"!';
$string['equiv_MISSINGVAR'] = 'Fehlende Zuweisungen';
$string['equiv_ASSUMEPOSVARS'] = 'Positive Variablen annehmen';
$string['equiv_ASSUMEPOSREALVARS'] = 'Positive reelle Variablen annehmen';
$string['equiv_LET'] = 'Lassen';

$string['equiv_AND'] = 'und';
$string['equiv_OR'] = 'oder';
$string['equiv_NOT'] = 'nicht';
$string['equiv_NAND'] = 'nand';
$string['equiv_NOR'] = 'nor';
$string['equiv_XOR'] = 'xor';
$string['equiv_XNOR'] = 'xnor';
$string['equiv_IMPLIES'] = 'impliziert';

$string['studentValidation_yourLastAnswer'] = 'Ihre letzte Antwort wurde wie folgt interpretiert: {$a}';
$string['studentValidation_listofvariables'] = 'Die Variablen, die in Ihrer Antwort gefunden wurden, waren: {$a}';
$string['studentValidation_listofunits'] = 'Die Einheiten, die in Ihrer Antwort gefunden wurden, waren: {$a}';
$string['studentValidation_invalidAnswer'] = 'Diese Antwort ist ungültig. ';
$string['studentValidation_notes'] = '(Diese Eingabe wird nicht automatisch von STACK bewertet.)';
$string['stackQuestion_noQuestionParts'] = 'Dieser Gegenstand hat keine Frageteile, die Sie beantworten müssen.';

$string['Interval_notinterval'] = 'Ein Intervall wurde erwartet, aber stattdessen haben wir {$a->m0}.';
$string['Interval_wrongnumargs'] = 'Die Konstruktion eines Intervalls muss genau zwei Argumente haben, daher muss dies ein Fehler sein: {$a->m0}.';
$string['Interval_backwards'] = 'Bei der Konstruktion eines reellen Intervalls müssen die Endpunkte geordnet sein. {$a->m0} sollte {$a->m1} sein.';
$string['Interval_illegal_entries'] = 'Das Folgende sollte nicht bei der Konstruktion von reellen Mengen erscheinen: {$a->m0}';

// Documentation strings.
$string['stackDoc_404']                 = 'Error 404';
$string['stackDoc_docs']                = 'STACK Documentation';
$string['stackDoc_docs_desc']           = 'The <a href="{$a->link}">documentation for STACK</a>: a local static wiki documenting the code you actually have running on your server.';
$string['stackDoc_home']                = 'Documentation home';
$string['stackDoc_index']               = 'Category index';
$string['stackDoc_siteMap']             = 'Site map';
$string['stackDoc_siteMap_en']          = 'English site map';
$string['stackDoc_404message']          = 'File not found.';
$string['stackDoc_directoryStructure']  = 'Directory structure';
$string['stackDoc_version']             = 'Your site is running STACK version {$a}.';
$string['stackDoc_licence']             = 'The STACK documentation is licensed under a <a rel="license" href="http://creativecommons.org/licenses/by-sa/4.0/">Creative Commons Attribution-ShareAlike 4.0 International License</a>.';
$string['stackDoc_licence_alt']         = 'Creative Commons License';
$string['stackDoc_AnswerTestResults']   = 'Answer test results

This page exposes the results of running answer tests on STACK test cases.  This page is automatically generated from the STACK unit tests and is designed to show question authors what answer tests actually do.  This includes cases where answer tests currentl fail, which gives a negative expected mark.  Comments and further test cases are very welcome.';

// Fact sheets.

$string['fact_sheet_preamble'] = '# Hints

STACK contains a "formula sheet" of useful fragments which a teacher may wish to include in a consistent way.  This is achieved through the "hints" system.

Hints can be included in any [CASText](CASText.md).

To include a hint, use the syntax

    [[facts:tag]]

The "tag" is chosen from the list below.

## All supported fact sheets

';

$string['greek_alphabet_name'] = 'The Greek Alphabet';
$string['greek_alphabet_fact'] = '||||
|--- |--- |--- |
|Upper case, \(\quad\)|lower case, \(\quad\)|name|
|\(A\)|\(\alpha\)|alpha|
|\(B\)|\(\beta\)|beta|
|\(\Gamma\)|\(\gamma\)|gamma|
|\(\Delta\)|\(\delta\)|delta|
|\(E\)|\(\epsilon\)|epsilon|
|\(Z\)|\(\zeta\)|zeta|
|\(H\)|\(\eta\)|eta|
|\(\Theta\)|\(\theta\)|theta|
|\(K\)|\(\kappa\)|kappa|
|\(M\)|\(\mu\)|mu|
|\(N\)|\( u\)|nu|
|\(\Xi\)|\(\xi\)|xi|
|\(O\)|\(o\)|omicron|
|\(\Pi\)|\(\pi\)|pi|
|\(I\)|\(\iota\)|iota|
|\(P\)|\(\rho\)|rho|
|\(\Sigma\)|\(\sigma\)|sigma|
|\(\Lambda\)|\(\lambda\)|lambda|
|\(T\)|\(\tau\)|tau|
|\(\Upsilon\)|\(\upsilon\)|upsilon|
|\(\Phi\)|\(\phi\)|phi|
|\(X\)|\(\chi\)|chi|
|\(\Psi\)|\(\psi\)|psi|
|\(\Omega\)|\(\omega\)|omega|';

$string['alg_inequalities_name'] = 'Inequalities';
$string['alg_inequalities_fact'] = '\[a>b \hbox{ means } a \hbox{ is greater than } b.\]
\[ a < b \hbox{ means } a \hbox{ is less than } b.\]
\[a\geq b \hbox{ means } a \hbox{ is greater than or equal to } b.\]
\[a\leq b \hbox{ means } a \hbox{ is less than or equal to } b.\]';

$string['alg_indices_name'] = 'The Laws of Indices';
$string['alg_indices_fact'] = 'The following laws govern index manipulation:
\[a^ma^n = a^{m+n}\]
\[\frac{a^m}{a^n} = a^{m-n}\]
\[(a^m)^n = a^{mn}\]
\[a^0 = 1\]
\[a^{-m} = \frac{1}{a^m}\]
\[a^{\frac{1}{n}} = \sqrt[n]{a}\]
\[a^{\frac{m}{n}} = \left(\sqrt[n]{a}\right)^m\]';

$string['alg_logarithms_name'] = 'The Laws of Logarithms';
$string['alg_logarithms_fact'] = 'For any base \(c>0\) with \(c \neq 1\):
\[\log_c(a) = b \mbox{, means } a = c^b\]
\[\log_c(a) + \log_c(b) = \log_c(ab)\]
\[\log_c(a) - \log_c(b) = \log_c\left(\frac{a}{b}\right)\]
\[n\log_c(a) = \log_c\left(a^n\right)\]
\[\log_c(1) = 0\]
\[\log_c(c) = 1\]
The formula for a change of base is:
\[\log_a(x) = \frac{\log_b(x)}{\log_b(a)}\]
Logarithms to base \(e\), denoted \(\log_e\) or alternatively \(\ln\) are called natural logarithms.  The letter \(e\) represents the exponential constant which is approximately \(2.718\).';

$string['alg_quadratic_formula_name'] = 'The Quadratic Formula';
$string['alg_quadratic_formula_fact'] = 'If we have a quadratic equation of the form:
\[ax^2 + bx + c = 0,\]
then the solution(s) to that equation given by the quadratic formula are:
\[x = \frac{-b \pm \sqrt{b^2 - 4ac}}{2a}.\]';

$string['alg_partial_fractions_name'] = 'Partial Fractions';
$string['alg_partial_fractions_fact'] = 'Proper fractions occur with \[{\frac{P(x)}{Q(x)}}\]
when \(P\) and \(Q\) are polynomials with the degree of \(P\) less than the degree of \(Q\).  This this case, we proceed
as follows: write \(Q(x)\) in factored form,

* a <em>linear factor</em> \(ax+b\) in the denominator produces a partial fraction of the form \[{\frac{A}{ax+b}}.\]
* a <em>repeated linear factors</em> \((ax+b)^2\) in the denominator
produce partial fractions of the form \[{A\over ax+b}+{B\over (ax+b)^2}.\]
* a <em>quadratic factor</em> \(ax^2+bx+c\)
in the denominator produces a partial fraction of
the form \[{Ax+B\over ax^2+bx+c}\]
* <em>Improper fractions</em> require an additional
term which is a polynomial of degree \(n-d\) where \(n\) is
the degree of the numerator (i.e. \(P(x)\)) and \(d\) is the degree of
the denominator (i.e. \(Q(x)\)).
';

$string['trig_degrees_radians_name'] = 'Degrees and Radians';
$string['trig_degrees_radians_fact'] = '\[
360^\circ= 2\pi \hbox{ radians},\quad
1^\circ={2\pi\over 360}={\pi\over 180}\hbox{ radians}
\]
\[
1 \hbox{ radian} = {180\over \pi} \hbox{ degrees}
\approx 57.3^\circ
\]';

$string['trig_standard_values_name'] = 'Standard Trigonometric Values';
$string['trig_standard_values_fact'] = '
\[\sin(45^\circ)={1\over \sqrt{2}}, \qquad \cos(45^\circ) = {1\over \sqrt{2}},\qquad
\tan( 45^\circ)=1
\]
\[
\sin (30^\circ)={1\over 2}, \qquad \cos (30^\circ)={\sqrt{3}\over 2},\qquad
\tan (30^\circ)={1\over \sqrt{3}}
\]
\[
\sin (60^\circ)={\sqrt{3}\over 2}, \qquad \cos (60^\circ)={1\over 2},\qquad
\tan (60^\circ)={ \sqrt{3}}
\]';

$string['trig_standard_identities_name'] = 'Standard Trigonometric Identities';
$string['trig_standard_identities_fact'] = '\[\sin(a\pm b)\ = \  \sin(a)\cos(b)\ \pm\  \cos(a)\sin(b)\]
 \[\cos(a\ \pm\ b)\ = \  \cos(a)\cos(b)\ \mp \\sin(a)\sin(b)\]
 \[\tan (a\ \pm\ b)\ = \  {\tan (a)\ \pm\ \tan (b)\over1\ \mp\ \tan (a)\tan (b)}\]
 \[ 2\sin(a)\cos(b)\ = \  \sin(a+b)\ +\ \sin(a-b)\]
 \[ 2\cos(a)\cos(b)\ = \  \cos(a-b)\ +\ \cos(a+b)\]
 \[ 2\sin(a)\sin(b) \ = \  \cos(a-b)\ -\ \cos(a+b)\]
 \[ \sin^2(a)+\cos^2(a)\ = \  1\]
 \[ 1+{\rm cot}^2(a)\ = \  {\rm cosec}^2(a),\quad \tan^2(a) +1 \ = \  \sec^2(a)\]
 \[ \cos(2a)\ = \  \cos^2(a)-\sin^2(a)\ = \  2\cos^2(a)-1\ = \  1-2\sin^2(a)\]
 \[ \sin(2a)\ = \  2\sin(a)\cos(a)\]
 \[ \sin^2(a) \ = \  {1-\cos (2a)\over 2}, \qquad \cos^2(a)\ = \  {1+\cos(2a)\over 2}\]';

$string['hyp_functions_name'] = 'Hyperbolic Functions';
$string['hyp_functions_fact'] = 'Hyperbolic functions have similar properties to trigonometric functions but can be represented in exponential form as follows:
 \[ \cosh(x)      = \frac{e^x+e^{-x}}{2}, \qquad \sinh(x)=\frac{e^x-e^{-x}}{2} \]
 \[ \tanh(x)      = \frac{\sinh(x)}{\cosh(x)} = \frac{{e^x-e^{-x}}}{e^x+e^{-x}} \]
 \[ {\rm sech}(x) ={1\over \cosh(x)}={2\over {\rm e}^x+{\rm e}^{-x}}, \qquad  {\rm cosech}(x)= {1\over \sinh(x)}={2\over {\rm e}^x-{\rm e}^{-x}} \]
 \[ {\rm coth}(x) ={\cosh(x)\over \sinh(x)} = {1\over {\rm tanh}(x)} ={{\rm e}^x+{\rm e}^{-x}\over {\rm e}^x-{\rm e}^{-x}}\]';

$string['hyp_identities_name'] = 'Hyperbolic Identities';
$string['hyp_identities_fact'] = 'The similarity between the way hyperbolic and trigonometric functions behave is apparent when observing some basic hyperbolic identities:
  \[{\rm e}^x=\cosh(x)+\sinh(x), \quad {\rm e}^{-x}=\cosh(x)-\sinh(x)\]
  \[\cosh^2(x) -\sinh^2(x) = 1\]
  \[1-{\rm tanh}^2(x)={\rm sech}^2(x)\]
  \[{\rm coth}^2(x)-1={\rm cosech}^2(x)\]
  \[\sinh(x\pm y)=\sinh(x)\ \cosh(y)\ \pm\ \cosh(x)\ \sinh(y)\]
  \[\cosh(x\pm y)=\cosh(x)\ \cosh(y)\ \pm\ \sinh(x)\ \sinh(y)\]
  \[\sinh(2x)=2\,\sinh(x)\cosh(x)\]
  \[\cosh(2x)=\cosh^2(x)+\sinh^2(x)\]
  \[\cosh^2(x)={\cosh(2x)+1\over 2}\]
  \[\sinh^2(x)={\cosh(2x)-1\over 2}\]';

$string['hyp_inverse_functions_name'] = 'Inverse Hyperbolic Functions';
$string['hyp_inverse_functions_fact'] = '\[\cosh^{-1}(x)=\ln\left(x+\sqrt{x^2-1}\right) \quad \mbox{ for } x\geq 1\]
 \[\sinh^{-1}(x)=\ln\left(x+\sqrt{x^2+1}\right)\]
 \[\tanh^{-1}(x) = \frac{1}{2}\ln\left({1+x\over 1-x}\right) \quad \mbox{ for } -1< x < 1\]';

$string['calc_diff_standard_derivatives_name'] = 'Standard Derivatives';
$string['calc_diff_standard_derivatives_fact'] = 'The following table displays the derivatives of some standard functions.  It is useful to learn these standard derivatives as they are used frequently in calculus.

|\(f(x)\)|\(f\'(x)\)|
|--- |--- |
|\(k\), constant|\(0\)|
|\(x^n\), any constant \(n\)|\(nx^{n-1}\)|
|\(e^x\)|\(e^x\)|
|\(\ln(x)=\log_{\rm e}(x)\)|\(\frac{1}{x}\)|
|\(\sin(x)\)|\(\cos(x)\)|
|\(\cos(x)\)|\(-\sin(x)\)|
|\(\tan(x) = \frac{\sin(x)}{\cos(x)}\)|\(\sec^2(x)\)|
|\(cosec(x)=\frac{1}{\sin(x)}\)|\(-cosec(x)\cot(x)\)|
|\(\sec(x)=\frac{1}{\cos(x)}\)|\(\sec(x)\tan(x)\)|
|\(\cot(x)=\frac{\cos(x)}{\sin(x)}\)|\(-cosec^2(x)\)|
|\(\cosh(x)\)|\(\sinh(x)\)|
|\(\sinh(x)\)|\(\cosh(x)\)|
|\(\tanh(x)\)|\(sech^2(x)\)|
|\(sech(x)\)|\(-sech(x)\tanh(x)\)|
|\(cosech(x)\)|\(-cosech(x)\coth(x)\)|
|\(coth(x)\)|\(-cosech^2(x)\)|

 \[ \frac{d}{dx}\left(\sin^{-1}(x)\right) =  \frac{1}{\sqrt{1-x^2}}\]
 \[ \frac{d}{dx}\left(\cos^{-1}(x)\right) =  \frac{-1}{\sqrt{1-x^2}}\]
 \[ \frac{d}{dx}\left(\tan^{-1}(x)\right) =  \frac{1}{1+x^2}\]
 \[ \frac{d}{dx}\left(\cosh^{-1}(x)\right) =  \frac{1}{\sqrt{x^2-1}}\]
 \[ \frac{d}{dx}\left(\sinh^{-1}(x)\right) =  \frac{1}{\sqrt{x^2+1}}\]
 \[ \frac{d}{dx}\left(\tanh^{-1}(x)\right) =  \frac{1}{1-x^2}\]
';

$string['calc_diff_linearity_rule_name'] = 'The Linearity Rule for Differentiation';
$string['calc_diff_linearity_rule_fact'] = '\[{{\rm d}\,\over {\rm d}x}\big(af(x)+bg(x)\big)=a{{\rm d}f(x)\over {\rm d}x}+b{{\rm d}g(x)\over {\rm d}x}\quad a,b {\rm\  constant.}\]';

$string['calc_product_rule_name'] = 'The Product Rule';
$string['calc_product_rule_fact'] = 'The following rule allows one to differentiate functions which are
multiplied together.  Assume that we wish to differentiate \(f(x)g(x)\) with respect to \(x\).
\[ \frac{\mathrm{d}}{\mathrm{d}{x}} \big(f(x)g(x)\big) = f(x) \cdot \frac{\mathrm{d} g(x)}{\mathrm{d}{x}}  + g(x)\cdot \frac{\mathrm{d} f(x)}{\mathrm{d}{x}},\] or, using alternative notation, \[ (f(x)g(x))\' = f\'(x)g(x)+f(x)g\'(x). \]';

$string['calc_quotient_rule_name'] = 'The Quotient Rule';
$string['calc_quotient_rule_fact'] = 'The quotient rule for differentiation states that for any two differentiable functions \(f(x)\) and \(g(x)\),
 \[\frac{d}{dx}\left(\frac{f(x)}{g(x)}\right)=\frac{g(x)\cdot\frac{df(x)}{dx}\ \ - \ \ f(x)\cdot \frac{dg(x)}{dx}}{g(x)^2}. \]';

$string['calc_chain_rule_name'] = 'The Chain Rule';
$string['calc_chain_rule_fact'] = 'The following rule allows one to find the derivative of a composition of two functions.
Assume we have a function \(f(g(x))\), then defining \(u=g(x)\), the derivative with respect to \(x\) is given by:
\[\frac{df(g(x))}{dx} = \frac{dg(x)}{dx}\cdot\frac{df(u)}{du}.\]
Alternatively, we can write:
\[\frac{df(x)}{dx} = f\'(g(x))\cdot g\'(x).\]
';

$string['calc_rules_name'] = 'Calculus rules';
$string['calc_rules_fact']  = '<b>The Product Rule</b><br />The following rule allows one to differentiate functions which are
multiplied together.  Assume that we wish to differentiate \(f(x)g(x)\) with respect to \(x\).
\[ \frac{\mathrm{d}}{\mathrm{d}{x}} \big(f(x)g(x)\big) = f(x) \cdot \frac{\mathrm{d} g(x)}{\mathrm{d}{x}}  + g(x)\cdot \frac{\mathrm{d} f(x)}{\mathrm{d}{x}},\] or, using alternative notation, \[ (f(x)g(x))\' = f\'(x)g(x)+f(x)g\'(x). \]
<b>The Quotient Rule</b><br />The quotient rule for differentiation states that for any two differentiable functions \(f(x)\) and \(g(x)\),
\[\frac{d}{dx}\left(\frac{f(x)}{g(x)}\right)=\frac{g(x)\cdot\frac{df(x)}{dx}\ \ - \ \ f(x)\cdot \frac{dg(x)}{dx}}{g(x)^2}. \]
<b>The Chain Rule</b><br />The following rule allows one to find the derivative of a composition of two functions.
Assume we have a function \(f(g(x))\), then defining \(u=g(x)\), the derivative with respect to \(x\) is given by:
\[\frac{df(g(x))}{dx} = \frac{dg(x)}{dx}\cdot\frac{df(u)}{du}.\]
Alternatively, we can write:
\[\frac{df(x)}{dx} = f\'(g(x))\cdot g\'(x).\]
';

$string['calc_int_standard_integrals_name'] = 'Standard Integrals';
$string['calc_int_standard_integrals_fact'] = '

\[\int k\ dx = kx +c, \mbox{ where k is constant.}\]
\[\int x^n\ dx  = \frac{x^{n+1}}{n+1}+c, \quad (n\ne -1)\]
\[\int x^{-1}\ dx = \int {\frac{1}{x}}\ dx = \ln(|x|)+c = \ln(k|x|) = \left\{\matrix{\ln(x)+c & x>0\cr
\ln(-x)+c & x<0\cr}\right.\]

|\(f(x)\)|\(\int f(x)\ dx\)||
|--- |--- |--- |
|\(e^x\)|\(e^x+c\)||
|\(\cos(x)\)|\(\sin(x)+c\)||
|\(\sin(x)\)|\(-\cos(x)+c\)||
|\(\tan(x)\)|\(\ln(\sec(x))+c\)|\(-\frac{\pi}{2} < x < \frac{\pi}{2}\)|
|\(\sec x\)|\(\ln (\sec(x)+\tan(x))+c\)|\( -{\pi\over 2}< x < {\frac{\pi}{2}}\)|
|\(\mbox{cosec}(x)\)|\(\ln (\mbox{cose}c(x)-\cot(x))+c\quad\)   |\(0 < x < \pi\)|
|cot\(\,x\)|\(\ln(\sin(x))+c\)|\(0< x< \pi\)|
|\(\cosh(x)\)|\(\sinh(x)+c\)||
|\(\sinh(x)\)|\(\cosh(x) + c\)||
|\(\tanh(x)\)|\(\ln(\cosh(x))+c\)||
|\(\mbox{coth}(x)\)|\(\ln(\sinh(x))+c \)|\(x>0\)|
|\({1\over x^2+a^2}\)|\({1\over a}\tan^{-1}{x\over a}+c\)|\(a>0\)|
|\({1\over x^2-a^2}\)|\({1\over 2a}\ln{x-a\over x+a}+c\)|\(|x|>a>0\)|
|\({1\over a^2-x^2}\)|\({1\over 2a}\ln{a+x\over a-x}+c\)|\(|x|\)|
|\(\frac{1}{\sqrt{x^2+a^2}}\)|\(\sinh^{-1}\left(\frac{x}{a}\right) + c\)|\(a>0\)|
|\({1\over \sqrt{x^2-a^2}}\)|\(\cosh^{-1}\left(\frac{x}{a}\right) + c\)|\(x\geq a > 0\)|
|\({1\over \sqrt{x^2+k}}\)|\(\ln (x+\sqrt{x^2+k})+c\)||
|\({1\over \sqrt{a^2-x^2}}\)|\(\sin^{-1}\left(\frac{x}{a}\right)+c\)|\(-a\leq x\leq a\)|
';

$string['calc_int_linearity_rule_name'] = 'The Linearity Rule for Integration';
$string['calc_int_linearity_rule_fact'] = '\[\int \left(af(x)+bg(x)\right){\rm d}x = a\int\!\!f(x)\,{\rm d}x
\,+\,b\int \!\!g(x)\,{\rm d}x, \quad (a,b \, \, {\rm constant.})
\]';

$string['calc_int_methods_substitution_name'] = 'Integration by Substitution';
$string['calc_int_methods_substitution_fact'] = '\[
\int f(u){{\rm d}u\over {\rm d}x}{\rm d}x=\int f(u){\rm d}u
\quad\hbox{and}\quad \int_a^bf(u){{\rm d}u\over {\rm d}x}\,{\rm
d}x = \int_{u(a)}^{u(b)}f(u){\rm d}u.
\]';

$string['calc_int_methods_parts_name'] = 'Integration by Parts';
$string['calc_int_methods_parts_fact'] = '\[
\int_a^b u{{\rm d}v\over {\rm d}x}{\rm d}x=\left[uv\right]_a^b-
\int_a^b{{\rm d}u\over {\rm d}x}v\,{\rm d}x\]
or alternatively: \[\int_a^bf(x)g(x)\,{\rm d}x=\left[f(x)\,\int
g(x){\rm d}x\right]_a^b -\int_a^b{{\rm d}f\over {\rm
d}x}\left\{\int g(x){\rm d}x\right\}{\rm d}x.\]';

$string['calc_int_methods_parts_indefinite_name'] = 'Integration by Parts';
$string['calc_int_methods_parts_indefinite_fact'] = '\[
\int u{{\rm d}v\over {\rm d}x}{\rm d}x=uv- \int{{\rm d}u\over {\rm d}x}v\,{\rm d}x\]
or alternatively: \[\int f(x)g(x)\,{\rm d}x=f(x)\,\int
g(x){\rm d}x -\int {{\rm d}f\over {\rm d}x}\left\{\int g(x){\rm d}x\right\}{\rm d}x.\]';

$string['Illegal_singleton_power'] = 'This input requires a numeric value presented in one of the following forms: <code>{$a->forms}</code>';
$string['Illegal_singleton_floats'] = 'This input does not accept decimal numbers in the given form. This input requires a numeric value presented in one of the following forms: <code>{$a->forms}</code>';
$string['Illegal_singleton_integer'] = 'This input does not accept integer values. This input requires a numeric value presented in one of the following forms: <code>{$a->forms}</code>';

$string['castext_debug_header_key'] = 'Variable name';
$string['castext_debug_header_value_simp'] = 'Simplified value';
$string['castext_debug_header_value_no_simp'] = 'Value';
$string['castext_debug_header_disp_simp'] = 'Simplified displayed value';
$string['castext_debug_header_disp_no_simp'] = 'Displayed value';
$string['castext_debug_no_vars'] = 'This question has no question variables to debug!';

$string['castext_error_header'] = 'Rendering of text content failed.';
$string['castext_error_unevaluated'] = 'This text content was never evaluated.';
