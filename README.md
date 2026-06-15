![DHBWTraining](https://github.com/user-attachments/assets/bddef559-a84d-4516-95cc-888e464793ef)


# DHBWTraining Repository Object Plugin for ILIAS 8
It is compatible with the previous DHBWTraining plugin for ILIAS < 8.0 information and objects.

## Installation & Update
1. **Ensure you delete any previous DHBWTraining folder** in Customizing/global/plugins/Services/Repository/RepositoryObject/

2. Create subdirectories, if necessary for Customizing/global/plugins/Services/Repository/RepositoryObject/ or run the following script from the ILIAS root

```bash
mkdir -p Customizing/global/plugins/Services/Repository/RepositoryObject
cd Customizing/global/plugins/Services/Repository/RepositoryObject
```

3. Then, execute:

```bash
git clone https://github.com/surlabs/DHBWTraining.git ./DHBWTraining
cd DHBWTraining
git checkout release_8
```

Ensure you run composer install at platform root before you install/update the plugin
```bash
composer install --no-dev
```

Run ILIAS update script at platform root
```bash
php setup/setup.php update
```

**Ensure you don't ignore plugins at the ilias .gitignore files and don't use --no-plugins option at ILIAS setup**

# Authors
* Initially created by studer ag, switzerland
* Further maintained by fluxlabs ag, switzerland
* Revamped and currently maintained by SURLABS, spain [SURLABS](https://surlabs.com)


# Version History
* The version 10.x.x for **ILIAS 10** maintained by SURLABS can be found in the Github branch **release_10**
* The version 9.x.x for **ILIAS 9** maintained by SURLABS can be found in the Github branch **release_9**
* The version 8.x.x for **ILIAS 8** maintained by SURLABS can be found in the Github branch **release_8**
* The previous plugin versions for ILIAS <8 is archived. It can be found in https://github.com/fluxapps/DHBWTraining
