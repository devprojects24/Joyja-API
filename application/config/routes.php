<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
| example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
| http://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
| $route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
| $route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
| $route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples: my-controller/index -> my_controller/index
|   my-controller/my-method -> my_controller/my_method
*/
$route['default_controller'] = 'welcome';
$route['404_override'] = '';
$route['translate_uri_dashes'] = TRUE;

/*
| -------------------------------------------------------------------------
| Sample REST API Routes
| -------------------------------------------------------------------------
*/
//Auth
$route['v1/Auth/login'] 		= 'api/v1/Auth/Auth/login';
$route['v1/Auth/register'] 		= 'api/v1/Auth/Auth/register';
$route['v1/Auth/generateOtp'] 	= 'api/v1/Auth/Auth/generateOtp';
$route['v1/Auth/validateOtp'] 	= 'api/v1/Auth/Auth/validateOtp';
$route['v1/Auth/changePassword'] 	= 'api/v1/Auth/Auth/changePassword';
$route['v1/Auth/verifyEmail'] 	= 'api/v1/Auth/Auth/verifyEmail';
$route['v1/Auth/addAssocite'] 	= 'api/v1/Auth/Auth/addAssocite';
$route['v1/Auth/associate'] 	= 'api/v1/Auth/Auth/associate';

//Proposal
$route['v1/proposals'] 			= 'api/v1/Proposals/Proposals/proposals';
$route['v1/proposals/updateFavorites'] 	= 'api/v1/Proposals/Proposals/updateFavorites';
$route['v1/proposals/favorites'] 	= 'api/v1/Proposals/Proposals/favorites';
$route['v1/proposals/challengeDashboard'] 	= 'api/v1/Proposals/Proposals/challengeDashboard';
$route['v1/proposals/startChallenge'] 	= 'api/v1/Proposals/Proposals/startChallenge';
$route['v1/proposals/challengeCalendar'] 	= 'api/v1/Proposals/Proposals/challengeCalendar';
$route['v1/proposals/viewChallenge'] 	= 'api/v1/Proposals/Proposals/viewChallenge';
$route['v1/proposals/addChallenge'] 	= 'api/v1/Proposals/Proposals/addChallenge';
$route['v1/proposals/challengeReport'] 	= 'api/v1/Proposals/Proposals/challengeReport';
$route['v1/proposals/deleteScore'] 	= 'api/v1/Proposals/Proposals/deleteScore';
$route['v1/proposals/reviews'] 	= 'api/v1/Proposals/Proposals/reviews';
$route['v1/proposals/challengeCertificate'] 	= 'api/v1/Proposals/Proposals/challengeCertificate';

//Orders
$route['v1/orders'] 			= 'api/v1/Orders/Orders/orders';
$route['v1/orders/updateReview'] = 'api/v1/Orders/Orders/updateReview';

//Blogs
$route['v1/blogs'] 				= 'api/v1/Blogs/Blogs/blogs';
$route['v1/blogs/updateComments'] 	= 'api/v1/Blogs/Blogs/updateComments';
$route['v1/blogs/updateLikes'] 	= 'api/v1/Blogs/Blogs/updateLikes';
$route['v1/blogs/categories'] 	= 'api/v1/Blogs/Blogs/categories';
$route['v1/blogs/subCategories'] 	= 'api/v1/Blogs/Blogs/subCategories';
$route['v1/blogs/addBlog'] 	= 'api/v1/Blogs/Blogs/addBlog';
$route['v1/blogs/updateBlog'] 	= 'api/v1/Blogs/Blogs/updateBlog';

//Users
$route['v1/users'] 		= 'api/v1/Users/Users/users';
$route['v1/users/notifications'] 	= 'api/v1/Users/Users/notifications';
$route['v1/users/addNotificiations'] 	= 'api/v1/Users/Users/addNotificiations';
$route['v1/users/updateNotifications'] 	= 'api/v1/Users/Users/updateNotifications';
$route['v1/users/deleteNotificiations'] = 'api/v1/Users/Users/deleteNotificiations';
$route['v1/users/addTicket'] = 'api/v1/Users/Users/addTicket';
$route['v1/users/tickets'] = 'api/v1/Users/Users/tickets';
$route['v1/users/postReply'] = 'api/v1/Users/Users/postReply';
$route['v1/users/closeTicket'] = 'api/v1/Users/Users/closeTicket';

//Tools
$route['v1/tools/bmi'] 		= 'api/v1/Tools/Tools/bmi';
$route['v1/tools/bmr'] 		= 'api/v1/Tools/Tools/bmr';
$route['v1/tools/calorie'] 		= 'api/v1/Tools/Tools/calorie';
$route['v1/tools/proglyco'] 		= 'api/v1/Tools/Tools/proglyco';
$route['v1/tools/prosympto'] 		= 'api/v1/Tools/Tools/prosympto';
