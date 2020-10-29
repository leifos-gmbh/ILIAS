# General Documentation

## Business Rules

### Assignment by Preferences

**Procedure**

See https://docu.ilias.de/goto_docu_wiki_wpage_5688_1357.html

Phase A

* Calcualte the popularity p(t) of each topic (number of users u that have choosen a topic)
* Choose topic t with lowest p(t); where p(t) > 0 (most unpopular topic)
* Randomly choose user u who has t as preference
* remove user and topic from list, start from the beginning

Phase B (only remaining users with no valid options)

* Choose random remaining user u
* Calculate number of assignments for each topic a(t)
* Assign t with minimum a(t) to u
* remove user and topic from list, start from the beginning