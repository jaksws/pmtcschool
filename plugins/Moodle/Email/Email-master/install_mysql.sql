
/**********************************************************************
 install_mysql.sql file
 Required as the module adds programs to other modules
 - Add profile exceptions for the module to appear in the menu
 - Add Email templates
***********************************************************************/

/*******************************************************
 profile_id:
 	- 0: student
 	- 1: admin
 	- 2: teacher
 	- 3: parent
 modname: should match the Menu.php entries
 can_use: 'Y'
 can_edit: 'Y' or null (generally null for non admins)
*******************************************************/
--
-- Data for Name: profile_exceptions; Type: TABLE DATA;
--

INSERT INTO profile_exceptions (profile_id, modname, can_use, can_edit)
SELECT 1, 'Email/EmailStudents.php', 'Y', 'Y'
FROM DUAL
WHERE NOT EXISTS (SELECT profile_id
    FROM profile_exceptions
    WHERE modname='Email/EmailStudents.php'
    AND profile_id=1);

INSERT INTO profile_exceptions (profile_id, modname, can_use, can_edit)
SELECT 1, 'Email/EmailUsers.php', 'Y', 'Y'
FROM DUAL
WHERE NOT EXISTS (SELECT profile_id
    FROM profile_exceptions
    WHERE modname='Email/EmailUsers.php'
    AND profile_id=1);

INSERT INTO profile_exceptions (profile_id, modname, can_use, can_edit)
SELECT 2, 'Email/EmailStudents.php', 'Y', 'Y'
FROM DUAL
WHERE NOT EXISTS (SELECT profile_id
    FROM profile_exceptions
    WHERE modname='Email/EmailStudents.php'
    AND profile_id=2);

INSERT INTO profile_exceptions (profile_id, modname, can_use, can_edit)
SELECT 2, 'Email/EmailUsers.php', 'Y', 'Y'
FROM DUAL
WHERE NOT EXISTS (SELECT profile_id
    FROM profile_exceptions
    WHERE modname='Email/EmailUsers.php'
    AND profile_id=2);


/*********************************************************
 Add Email template
**********************************************************/
--
-- Data for Name: templates; Type: TABLE DATA;
--

INSERT INTO templates (modname, staff_id, template)
SELECT 'Email/EmailStudents.php', 0, ''
FROM DUAL
WHERE NOT EXISTS (SELECT modname
    FROM templates
    WHERE modname='Email/EmailStudents.php'
    AND staff_id=0);

INSERT INTO templates (modname, staff_id, template)
SELECT 'Email/EmailUsers.php', 0, ''
FROM DUAL
WHERE NOT EXISTS (SELECT modname
    FROM templates
    WHERE modname='Email/EmailUsers.php'
    AND staff_id=0);
