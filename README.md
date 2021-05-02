
# ⚶ Vesta Classic Look & Feel (Webtrees 2 Custom Module)

This [webtrees](https://www.webtrees.net/) custom module adjusts all themes and other features, providing a look & feel closer to the webtrees 1.x version. 
The project’s website is [cissee.de](https://cissee.de). 

The module was formerly known as 'Compact Themes Adjuster'. It is now part of the Vesta suite.

This is a webtrees 2.x module - It cannot be used with webtrees 1.x.

## Contents

* [Features](#features)
* [Download](#download)
* [Installation](#installation)
* [License](#license)

### Features<a name="features"/>

#### Layout

* The overall width is adjusted for larger resolutions, as suggested [here](https://www.webtrees.net/index.php/en/forum/3-help-for-2-0-alpha/32882-solved-support-for-bigger-monitors#70135)
* The individual page is adjusted further:
    * Smaller sidebar
    * Less padding between elements
    * The key-value pairs of the name parts are inline
    * Gender information is moved to the header (as an icon)
    * Media edit controls is moved to the edit menu (a better place for these edit controls may be the Media tab itself)

default 'webtrees' theme   |  adjusted 'webtrees' theme
:-------------------------:|:-------------------------:
![Screenshot](individual.png) | ![Screenshot](individual_compact.png)

* All edit dialogs are also displayed in a more compact layout.
* Note that this module itself is not a theme: The webtrees user will not be able to switch between the compact and the regular layout! All layout adjustments are globally configurable though.
* Further suggestions are very welcome!

#### Functionality

* The module optionally displays nicknames as in webtrees 1.x (before the surname). See [here](https://github.com/fisharebest/webtrees/issues/1401) for the related discussion.
* The module allows to use xrefs with specific prefixes, as in webtrees 1.x. See [e.g. here](https://www.webtrees.net/index.php/en/forum/help-for-2-0/33978-identities-in-gedcom-file) for the related discussion.

### Download<a name="download"/>

* Current version: 2.0.15.4.0
* Based on and tested with webtrees 2.0.15. Requires webtrees 2.0.12 or later.
* Requires the ⚶ Vesta Common module ('vesta_common').
* Download the zipped module, including all related modules, [here](https://cissee.de/vesta.latest.zip).
* Support, suggestions, feature requests: <ric@richard-cissee.de>
* Issues also via <https://github.com/vesta-webtrees-2-custom-modules/classic_laf/issues>
* Translations may be contributed via weblate: <https://hosted.weblate.org/projects/vesta-webtrees-custom-modules/>
 
### Installation<a name="installation"/>

* Unzip the files and copy the contents of the modules_v4 folder to the respective folder of your webtrees installation. All related modules are included in the zip file. It's safe to overwrite the respective directories if they already exist (they are bundled with other custom modules as well), as long as other custom models using these dependencies are also upgraded to their respective latest versions.
* Enable the main module via Control Panel -> Modules -> All modules -> ⚶ Vesta Classic Look & Feel. After that, you may configure some options.

### License<a name="license"/>

* **vesta_classic_look_and_feel: a webtrees custom module**
* Copyright (C) 2020 – 2021 Richard Cissée
* Derived from **webtrees** - Copyright 2021 webtrees development team.
* Dutch translations provided by TheDutchJewel.
* Czech translations provided by Josef Prause.

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
