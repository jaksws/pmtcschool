/**
 * Delete SQL
 *
 * Required if install.sql file present
 * - Delete profile exceptions
 * - Delete module specific tables
 * (and their eventual sequences & indexes) if any
 *
 * @package Reports module
 */

--
-- Delete from profile_exceptions table
--

DELETE FROM profile_exceptions WHERE modname='Reports/SavedReports.php';
DELETE FROM profile_exceptions WHERE modname='Reports/Calculations.php';
DELETE FROM profile_exceptions WHERE modname='Reports/CalculationsReports.php';
DELETE FROM profile_exceptions WHERE modname LIKE 'Reports/RunReport.php%';


--
-- Delete saved_calculations table
--

DROP TABLE IF EXISTS saved_calculations;


--
-- Delete saved_reports table
--

DROP TABLE IF EXISTS saved_reports;
