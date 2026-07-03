# Staff Privacy

This documentation does not warrant completeness or correctness. Please report any
missing or wrong information using the [ILIAS issue tracker](https://mantis.ilias.de)
or contribute a fix via [Pull Request](../../../docs/development/contributing.md#pull-request-to-the-repositories).

## Integrated Services

The Staff component aggregates data from various ILIAS services. Please consult the respective privacy documentation:

- [OrgUnit](../OrgUnit/PRIVACY.md) provides information about users' assignment to **organisational units**,
  what users they have authority over, and which data this authority grants access to via 
  the **position access** mechanism. 
- [Skill](../Skill/PRIVACY.md) provides information about **achieved skill levels**, and a pre-built UI
  showing that information for a given user.
- **User** handles **user identification**, provides **user profile data** and whether a profile
  is **published**, and provides a list of user-specific actions.
- **Tree** is used together with **ILIASObject** to check the hierarchy of **organisational units**.
- [AccessControl](../AccessControl/PRIVACY.md) is used to check permissions to decide whether objects can be linked to.
- **Tracking** provides information the **learning progress status** of users in courses.
- [Course](../Course/PRIVACY.md) together with **Membership** provides information about user **enrolment status 
  in courses**.
- **ILIASObject** is used to retrieve **titles** of courses.
- [Certificate](../Certificate/PRIVACY.md) provides information about **awarded certificates**,
  and a pre-built UI showing that information for a given user.

## General Information

**Staff** provides a number of reporting views to give users
an overview over the status of everyone they have authority over
(as defined via organisational units). This can include user profile
data, users' enrolment and learning progress status in courses,
their achieved skill levels, and the certificates that have been
issued to them.

Which views are shown, and what data is shown in them, depends on the position access configuration
of **OrgUnit**.

Note that a view by [EmployeeTalk](../EmployeeTalk/PRIVACY.md) is offered in the same main
menu entry, see that component for the related privacy information.

## Data being stored

The component does not store any data itself, it only aggregates
data stored elsewhere.

## Data being presented

The component offers different views that show different data
of all users that the current user has authority over via the
organisational units.

Additionally, views that report individually on single users
under the authority of the current user are also available,
see [below](#user-specific).

All views are only available if 'Enable Main Menu Entry' is
enabled in the **OrgUnit** settings, and if the current user
has authority over at least one user.

### Staff List

The Staff List presents the following **user profile** data of
all relevant users:

- **Avatar**
- **Login**
- All other **user profile fields** of type 'Default',
  and set to 'Searchable' in the **User** profile administration.

Further, a selection of user-specific actions is available,
which exposes the users' **user ID**.

### Course Memberships

'Course Memberships' is only available if the current user has the
'Manage Members' permission in courses over at least one user under
their authority, as granted by position access in the **OrgUnit**
administration. This permission can be set as a default per position,
but can also be overwritten locally in individual courses.

It shows a table of those **course enrolments** of users where
the current user has the 'Manage Members' permission over that
user in that course. Included is the following data:

- **Title** of the course.
- **Login** of the user.
- **First Name**, **Last Name**, **E-Mail**, and **Organisational Units**
  of the user, only if the corresponding **user profile field** is
  set to 'Searchable' via the **User** component..
- **Member Status** of the user in the course: 'Registered', 'Waiting List',
  'Requested'
- **Learning Progress** status of the user in the course, only if
  the learning progress is active on the installation, and the
  current user has the 'View learning progress of other users'
  position permission over the user in the course.

### Certificates

'Certificates' is only available if certificates are activated
on the installation. Also, the current user must have the
'View certificates of other users' permission in courses, exercises,
or tests over at least one user under their authority, as granted
by position access in the **OrgUnit** administration. This permission
can be set as a default per position, but can also be overwritten locally in
individual courses, exercises, and tests.

It presents data related to certificates achieved by users
courses, exercises, or tests. For a competence to appear,
the current users must have the 'View certificates of other users'
permission over that user in that object.

A table of all such **certificates** of those users is shown,
with the following data:

- **Title** of the object in which the certificate was awarded.
- **Issued On:** Date on which the certificate was awarded.
- **Login** of the user to which the certificate was awarded.
- **First Name**, **Last Name**, **E-Mail**, and **Organisational Units**
  of the user, only if the corresponding **user profile field** is
  set to 'Searchable' via the **User** component..

### Competences

'Competences' is only available if competence management is
activated on the installation. Also, the current user must have the
'View competences of other users' permission in courses, groups, surveys,
or tests over at least one user under their authority, as granted
by position access in the **OrgUnit** administration. This permission
can be set as a default per position, but can also be overwritten locally in
individual courses, groups, surveys, and tests

It presents data related to competences achieved by users
in courses, groups, surveys, or tests. For a competence to appear,
the current users must have the 'View competences of other users'
permission over that user in that object.

A table of all such **certificates** of those users is shown,
with the following data:

- **Competence:** Title of the competence.
- **Competence Level:** Title of the achieved level in the competence.
- **Login** of the user.
- **First Name**, **Last Name**, **E-Mail**, and **Organisational Units**
  of the user, only if the corresponding **user profile field** is
  set to 'Searchable' via the **User** component.

### User-Specific

All user-specific views always show the **avatar** and the **login**
of the selected user. They also show their **first name** and
**last name**, if the users' profile is published.

The following user-specific views are offered:

- **Course Memberships:** Same as the [Course Memberships](#course-memberships) overview,
  but without the fields **Login**, **First Name**, **Last Name**,
  **E-Mail**, and **Organisational Units**. Available under the same conditions.
- **Certificates:** All certificates of the user, regardless of position
  access, as presented to the user by the **Certificate** component
  via 'Achievements > Certificates'. Available under the same conditions as the
  [Certificates](#certificates) overview.
- **Competences:** All competences of the user, regardless of position
  access, as presented to the user by the **Skill** component 
  via 'Achievements > Competences > Selected Competences'.
  Available under the same conditions as the [Competences](#competences) overview.
- **Profile:** The profile of the user supplied by the **User** component. Only
  available if the user's profile is published.

Note that a view by [EmployeeTalk](../EmployeeTalk/PRIVACY.md) is also
offered here, see that component for the related privacy information.

## Data being deleted

The component does not store any data itself. Its behaviour when
deleting e.g. users or courses depends on the behaviour of the
related integrated component.

## Data being exported

Many of the tables offered in the component can be exported
in Excel or CSV format. The data being exported matches the 
data shown in the UI, see [above](#data-being-presented).

The following views contain exportable tables:

- Staff List
- Course Membership (both the overview, and the user-specific view)
- Certificates (overview only)
- Competences (overview only)

Additionally, in the 'Certificates' views (user-specific and
overview) the certificates awarded to users can be downloaded.
