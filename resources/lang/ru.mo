��    H      \  a   �         k   !  5   �  m   �  Z   1  ,   �     �     �     �  j   �  �   c  k   �  U   S	  '   �	  #   �	     �	     
  /   
  f   E
  ,   �
  !   �
     �
          #     7  +   L     x  @   �  G   �       �        �     �       
   
       W   !  I   y     �     �  	   �  c   �  �   Z  �   �     �     �  k   �  )     +   8  &   d  A   �  �   �  X   `  6   �     �       #     Z   ;     �  1  �  ]   �     :     H    N     U     Z  =   f  *   �     �  9   �          #    B  �   ]  u   S  �   �  �   �  c   x  +   �  :     >   C  �   �  $  1  �   V  �   3  :      7   <      t   1   �   o   �   (  4!  X   ]"  G   �"  )   �"  H   (#  7   q#  =   �#  }   �#     e$  �   y$  �   %  #   �%  i  �%  -   6'  8   d'     �'     �'     �'  �   �'  �   �(  .   9)  A   h)     �)  �   �)  -  �*    �+     �,  '   �,    -  g   .  U   �.  f   �.  �   C/  =  �/  �   #1  �   �1  D   f2  6   �2  n   �2  �   Q3  :   .4  @  i4  �   �6     �7     �7    �7  
   �9     �9  �   �9  �   �:  !   K;  �   m;  !   �;  [   <     '       /   3      1       
   .          <                    &   %      )      6         8      *                     (          F      B   !          >   ;      ,   E   D   9                 7   	   4   "              =      +   :      0          C   H                  ?       #   2                        $   A       G          5   @   -               'Multi-fact dialogs' refers to: All edit dialogs for new individuals, and the Vesta-specific dialog '%1$s'. A label, for reference within the control panel only. A module adjusting all themes and other features, providing a look & feel closer to the webtrees 1.x version. A name badge can be displayed on just one of the family trees, or on all the family trees. A note, shown within the control panel only. Add a name badge Always expand initially Append XREFs to names Apply the following setting to '%1$s' within all '%2$s' events, unless the event is configured explicitly: Attention: This setting currently won't have any effect in your system, because it requires a newer libxml version (at least %1$s). Check this option if you prefer not to use a name type in case the respective individual has a single name. Check to always expand the first sidebar, rather than the 'Family navigator' sidebar. Configure visibility of GEDCOM tag '%s' Configure visibility of GEDCOM tags Crop Thumbnails Custom prefixes Display a family's XREF after the family label. Display all edit dialogs using a more compact layout, which also omits the standard header and footer. Display an individual's XREF after the name. Display nicknames before surnames Edit dialogs Edit main facts and events Edit the name badge Expand first sidebar Expand initially if name has note or source HTML snippet HTML snippet to be displayed after the name, e.g. a small image. Handle nicknames as in webtrees 1.x, i.e. show them before the surname. Image Thumbnails In a family tree, each record has an internal reference number (called an "XREF") such as "F123" or "R14". You can choose the prefix that will be used whenever new XREFs are created. Individual page Individual page: Name blocks Layout Name badge Name badges Name badges are HTML snippets, e.g. small images, displayed after an individual's name. Name blocks are always expandable/collapsible regardless of this setting. Name type presets Never expand initially Nicknames Note that currently there is no way to match on linked objects, such as the type of a media object. Note that in addition to the configuration options below, %1$s allows you to configure the visibility of each specific tag in detail, as described %2$s. Note that this doesn't affect GEDCOM name fields that already include a nickname, i.e. you may always position the nickname explicitly for specific names. Overall width Regex Regular expression (PCRE2 syntax including slashes as delimiters) to match an individual's raw gedcom data. See the initial name badges for examples. Several adjustments - See %1$s for details. Skip name type preset for single names Syntactically invalid regex expressions will be silently ignored. The standard layout centers most pages, wasting a lot of space especially on wide displays. This option allows to use most of the available space. They are selected based on regular expressions matching an individual's raw gedcom data. This only affects standard and specific custom themes. Use available space Use compact layout Use compact layout except for names Use custom prefixes for XREFs as in webtrees 1.x, instead of prefixing all XREFs with 'X'. Use original layout Webtrees crops thumbnails in order to produce images with a consistent width and height. This is problematic if you have images of individals with a non-standard aspect ratio, where the head of the respective person is not centered and may therefore be cut off. Deselect this option to handle these cases. When adding new parents, spouses or children, webtrees presets the name type to 'birth name'. XREF prefixes XREFs You can use them to highlight individuals, in charts and elsewhere, via different criteria, such as: Individuals belonging to a group defined via a shared note, having a sourced birth event, having a specific occupation, having a burial with a linked image, etc. here hide always hide in multi-fact dialogs, show and expand subtags otherwise hide in multi-fact dialogs, show otherwise no adjustment no adjustment (use setting for all events as shown below) show always show and expand subtags always Project-Id-Version: Russian (Vesta Webtrees Custom Modules)
Report-Msgid-Bugs-To: 
PO-Revision-Date: YEAR-MO-DA HO:MI+ZONE
Last-Translator: FULL NAME <EMAIL@ADDRESS>
Language-Team: Russian <https://hosted.weblate.org/projects/vesta-webtrees-custom-modules/vesta-classic-look-and-feel/ru/>
Language: ru
MIME-Version: 1.0
Content-Type: text/plain; charset=UTF-8
Content-Transfer-Encoding: 8bit
Plural-Forms: nplurals=3; plural=(n%10==1 && n%100!=11 ? 0 : n%10>=2 && n%10<=4 && (n%100<10 || n%100>=20) ? 1 : 2);
X-Generator: Weblate 5.9-dev
 "Диалоги с несколькими фактами" относятся ко всем диалогам редактирования для новых пользователей и специфичному для Vesta диалогу "%1$s". Метка, предназначенная только для справки на панели управления. Модуль, настраивающий все темы и другие возможности, обеспечивающий внешний вид и ощущения, приближенные к версии webtrees 1.x. Именной значок может быть отображен только на одном из генеалогических древ или на всех генеалогических древах. Примечание, отображаемое только на панели управления. Добавьте бейдж с именем Всегда расширяйтесь изначально Добавлять внешние ссылки к именам Примените следующий параметр к '%1$s' во всех событиях '%2$s', если только событие не настроено явно: Внимание: этот параметр в настоящее время не будет иметь никакого эффекта в вашей системе, потому что для него требуется более новая версия libxml (как минимум %1$s). Установите этот флажок, если вы предпочитаете не использовать тип имени в случае, если у соответствующего лица одно имя. Установите этот флажок, чтобы всегда открывать первую боковую панель, а не боковую панель «Семейный навигатор». Настройка видимости тега GEDCOM '%s' Настройка видимости тегов GEDCOM Обрезать эскизы Пользовательские префиксы Отображение внешней ссылки семейства после метки семейства. Отобразите все диалоговые окна редактирования с использованием более компактного макета, в котором также отсутствуют стандартные верхний и нижний колонтитулы. Отображать внешнюю ссылку человека после имени. Отображать псевдонимы перед фамилиями Редактировать диалоги Редактировать основные факты и события Отредактируйте бейдж с именем Разверните первую боковую панель Разверните изначально, если в названии есть примечание или источник Сниппет HTML Фрагмент HTML, который будет отображаться после названия, например, небольшое изображение. Обращайтесь с псевдонимами как в webtrees 1.x, т.е. показывайте их перед фамилией. Эскизы изображений В семейном древе каждая запись имеет внутренний ссылочный номер (называемый «XREF»), например «F123» или «R14». Вы можете выбрать префикс, который будет использоваться при создании новых внешних ссылок. Индивидуальная страница Отдельная страница: Блоки имен Вкладки Именной значок Именные бейджи Именные бейджи - это фрагменты HTML, например, небольшие изображения, отображаемые после имени пользователя. Блоки имен всегда можно расширять / сворачивать независимо от этого параметра. Предустановки типа имени Никогда не расширяйтесь изначально Никнеймы Обратите внимание, что в настоящее время нет способа сопоставления связанных объектов, таких как тип медиаобъекта. Обратите внимание, что в дополнение к приведенным ниже параметрам конфигурации %1$s позволяет детально настроить видимость каждого конкретного тега, как описано %2$s. Обратите внимание, что это не влияет на поля имени GEDCOM, которые уже включают псевдоним, т.е. вы всегда можете явно указать псевдоним для определенных имен. Общая ширина Регулярное выражение Регулярное выражение (синтаксис PCRE2, включающий косые черты в качестве разделителей) для соответствия необработанным данным gedcom пользователя. Примеры приведены на бейджах с первоначальными именами. Несколько корректировок - подробности см. В %1$s. Пропустить предустановку типа имени для одиночных имен Синтаксически недопустимые регулярные выражения будут автоматически проигнорированы. Стандартный макет центрирует большинство страниц, тратя много места, особенно на широких дисплеях. Эта опция позволяет использовать большую часть доступного пространства. Они выбираются на основе регулярных выражений, соответствующих необработанным данным gedcom пользователя. Это влияет только на стандартные и специальные пользовательские темы. Использовать доступное пространство Используйте компактный макет Используйте компактную компоновку, за исключением названий Используйте настраиваемые префиксы для внешних ссылок, как в webtrees 1.x, вместо того, чтобы ставить перед всеми ссылками XREF. Используйте оригинальный макет Webtrees обрезает эскизы, чтобы создавать изображения одинаковой ширины и высоты. Это проблематично, если у вас есть изображения людей с нестандартным соотношением сторон, где голова соответствующего человека не находится по центру и поэтому может быть отрезана. Снимите этот флажок, чтобы обрабатывать такие случаи. При добавлении новых родителей, супругов или детей webtrees предварительно устанавливает тип имени на «имя при рождении». Префиксы XREF Внешние ссылки Вы можете использовать их для выделения отдельных лиц на диаграммах и в других местах по различным критериям, таким как: Принадлежность к группе, определенной с помощью общей заметки, наличие события рождения, определенного рода занятий, захоронения со связанным изображением и т.д. здесь прятаться всегда скрывать в диалоговых окнах с несколькими фактами, показывать и разворачивать подзаголовки в противном случае скрывать в диалоговых окнах с несколькими фактами, показывать в противном случае без корректировки без настройки (используйте настройку для всех событий, как показано ниже) показывать всегда всегда показывать и разворачивать вложенные теги 