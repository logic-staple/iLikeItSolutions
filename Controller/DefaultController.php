<?php

namespace iLikeItSolutions\GoogleAnalyticsApiBundle\Controller;

use Google_Service_Analytics;
use Google_Service_AnalyticsReporting_DateRange;
use Google_Service_AnalyticsReporting_GetReportsRequest;
use Google_Service_AnalyticsReporting_Metric;
use Google_Service_AnalyticsReporting_ReportRequest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

//https://developers.google.com/analytics/devguides/config/mgmt/v3/quickstart/web-php
class DefaultController extends Controller
{
    /**
     * @Route("/analytic", name="homepage")
     * @Template()
     * purpose: Fetch all dashboard data using GA API
     */
   	
    public function indexAction(Request $requestObj)
    {
    	$session = $this->get('session');
    	/* Get access token detail from file and refresh if token expired */
        $tokenFileLocation = __DIR__.'/../Data/token.json';

        $clientFileLocation = __DIR__.'/../Data/client_secrets.json';
        $isClientFileExist = 0;

        if(file_exists($clientFileLocation)){
        	$isClientFileExist = 1;
        }

        $viewId = $this->getParameter('ga_view_id');

        /* Check first Client Json file exist */
        if($isClientFileExist == 0 || empty($viewId)){
        	return $this->render('GoogleAnalyticsApiBundle:Default:index.html.twig', array(
        			'isClientFileExist' => $isClientFileExist,
        			'viewId' => $viewId
        		));
        }

        /* Check Token JSON file exist if not then redirect to auth first time from GA */
        if(!file_exists($tokenFileLocation)){
        	return $this->redirectToRoute('google_analytics_api_callback', array(), 301);
        }
        $tokenJSONData = file_get_contents($tokenFileLocation); 
        $tokenData = json_decode($tokenJSONData, true);
        $acessToken = isset($tokenData['access_token'])?$tokenData['access_token']:"";
        $refreshToken = isset($tokenData['refresh_token'])?$tokenData['refresh_token']:"";
        
    	if(!empty($acessToken)){
	        $analyticsService = $this->get('google_analytics_api.api');
	    try{
        	$client = $analyticsService->getClient();
        	$client->setAccessToken(json_encode($tokenData));
        }catch(Exception $e){
        	echo "Exception: ".$e->getMessage();
        }
	        $isExpired = $client->isAccessTokenExpired();
	        if($isExpired){
				$new_access_token = $client->refreshToken($refreshToken);
				if(isset($new_access_token['access_token']) && !empty($new_access_token['access_token'])){
					$new_access_token = json_encode($new_access_token);
					file_put_contents($tokenFileLocation,$new_access_token);
					$redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . '/analytic';
					$redirect_uri =   filter_var($redirect_uri, FILTER_SANITIZE_URL);
					return $this->redirect($redirect_uri);
					
				}
			}
			$beginDate = date('Y-m-d', strtotime('-29 days'));
			$endDate = date('Y-m-d', time());
			if($requestObj->query->get('beginDate')){
				$beginDate = date("Y-m-d", strtotime($requestObj->query->get('beginDate')));
			}
			if($requestObj->query->get('endDate')){
				$endDate = date("Y-m-d", strtotime($requestObj->query->get('endDate')));
			}
			
	        $analytics = $analyticsService->getAnalytics();

	        // Create the DateRange object
	        $dateRange = new Google_Service_AnalyticsReporting_DateRange();
	        $dateRange->setStartDate($beginDate);
	        $dateRange->setEndDate($endDate);

	        // Create the Metrics object
	        $sessions = new Google_Service_AnalyticsReporting_Metric();
	        $sessions->setExpression("ga:sessions");
	        $sessions->setAlias("sessions");

	        // Create the ReportRequest object
	        $request = new Google_Service_AnalyticsReporting_ReportRequest();
	        $request->setViewId($viewId);
	        $request->setDateRanges($dateRange);
	        $request->setMetrics([$sessions]);

	        $body = new Google_Service_AnalyticsReporting_GetReportsRequest();
	        $body->setReportRequests([$request]);
	        $report = $analytics->reports->batchGet($body);

	        // above code included into this helper method :

	        $sessions = $analyticsService->getSessionsDateRange($viewId,$beginDate,$endDate);
	        $bounceRate = $analyticsService->getBounceRateDateRange($viewId,$beginDate,$endDate);
	        $avgTimeOnPage = $analyticsService->getAvgTimeOnPageDateRange($viewId,$beginDate,$endDate);
	        $pageViewsPerSession = $analyticsService->getPageviewsPerSessionDateRange($viewId,$beginDate,$endDate);
	        $percentNewVisits = $analyticsService->getPercentNewVisitsDateRange($viewId,$beginDate,$endDate);
	        $pageViews = $analyticsService->getPageViewsDateRange($viewId,$beginDate,$endDate);
	        $avgPageLoadTime = $analyticsService->getAvgPageLoadTimeDateRange($viewId,$beginDate,$endDate);
	        
	        $limit = 5; // set limit to 5 for table records
	        $trafficSourcesData = $trafficLocationData = $pagesVisitData = $geoLocationDataResult = $pagesSessionsData = array();
	        /* Get Traffic source data with domain name */
	       	$trafficSourcesData = $analyticsService->getTraffic($viewId,$beginDate,$endDate, 'ga:sessions', 'ga:source', $limit);
	        /* Get Traffic source data with location name */
	        $trafficLocationData = $analyticsService->getTraffic($viewId,$beginDate,$endDate, 'ga:sessions', 'ga:country', $limit);
	        /* Get Traffic source data with pages name */
	        $pagesVisitData = $analyticsService->getPagesVisits($viewId,$beginDate,$endDate);
	        $limit = "All";
	        /* Get all traffic sessions on different location for geo map */
	        $geoLocationDataResult = $analyticsService->getTraffic($viewId,$beginDate,$endDate, 'ga:sessions', 'ga:country', $limit);
	        $geoLocationData = array();
	        foreach($geoLocationDataResult as $d){
	       			$arr = array($d['source'], $d['value']);
	        		array_push($geoLocationData, $arr);
	        }

	        $currentWeek = date('W', time());
	    	$year = date('Y', time());
	    	$lastWeek = ($currentWeek - 1);
	    	$lastWeekStart = date("Y-m-d", strtotime("{$year}-W{$lastWeek}-0"));
	    	$lastWeekEnd = date("Y-m-d", strtotime("{$year}-W{$lastWeek}-6"));

	    	/* Get last week all days sessions/pagevisits line graph data */
	        $pagesSessionsData = $analyticsService->getPagesSessionWeekly($viewId,$lastWeekStart,$lastWeekEnd);
	
	        return $this->render('GoogleAnalyticsApiBundle:Default:index.html.twig', array(
	            'analytics'     =>  $analytics,
	            'client'        =>  $client,
	            'report'        =>  $report,
	            'beginDate'		=>  date("Y-m-d", strtotime($beginDate)),
	            'endDate'		=>  date("Y-m-d", strtotime($endDate)),
	            'isClientFileExist' => $isClientFileExist,
	            'viewId'		=> 	$viewId,
	            'data'          =>  [
	                'sessions'              =>  $sessions,
	                'bounce_rate'           =>  $bounceRate,
	                'avg_time_on_page'      =>  $avgTimeOnPage,
	                'page_view_per_session' =>  $pageViewsPerSession,
	                'percent_new_visits'    =>  $percentNewVisits,
	                'page_views'            =>  $pageViews,
	                'avg_page_load_time'    =>  $avgPageLoadTime,
	                'trafficSourcesData' 	=> 	$trafficSourcesData,
	                'trafficLocationData'	=>  $trafficLocationData,
	                'pagesVisitData'		=> 	$pagesVisitData,
	                'geoLocationData'       =>  $geoLocationData,
	                'pagesSessionsData'		=>  $pagesSessionsData
	            ]
	        ));
		}
		else 
		{
			//$redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . '/callback';
			return $this->redirectToRoute('google_analytics_api_callback', array(), 301);
		}
    }

    /**
     * Matches /callback exactly
     * Purpose: For authorized GA access from account first time and
     *			write recieved tokens to token.json file
     * @Route("/callback", name="google_analytics_api_callback")
     */
    public function callbackAction()
    {
    	$analyticsService = $this->get('google_analytics_api.api');
    	$client = $analyticsService->getClient();
    	try{
        	$client = $analyticsService->getClient();
        }catch(Exception $e){
        	echo "Exception: ".$e->getMessage();
        }
        // Handle authorization flow from the server.
		if (!isset($_GET['code']))
		{
			/* check first access token is not expired */
			$tokenFileLocation = __DIR__.'/../Data/token.json';
			/* Check Token JSON file exist if not then redirect to auth first time from GA */
        	if(file_exists($tokenFileLocation)){
		        $tokenJSONData = file_get_contents($tokenFileLocation); 
		        $tokenData = json_decode($tokenJSONData, true);
		        $acessToken = isset($tokenData['access_token'])?$tokenData['access_token']:"";
		        $refreshToken = isset($tokenData['refresh_token'])?$tokenData['refresh_token']:"";
	        	if(!empty($acessToken)){
	        		$isExpired = $client->isAccessTokenExpired();
			        if($isExpired){
						$new_access_token = $client->refreshToken($refreshToken);
						if(isset($new_access_token['access_token']) && !empty($new_access_token['access_token'])){
							$new_access_token = json_encode($new_access_token);
							file_put_contents($tokenFileLocation,$new_access_token);
							$redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . '/analytic';
							$redirect_uri =   filter_var($redirect_uri, FILTER_SANITIZE_URL);
							return $this->redirect($redirect_uri);
						}
					}
	        	}
        	}

			$auth_url = $client->createAuthUrl();
			$auth_url = filter_var($auth_url, FILTER_SANITIZE_URL);
			return $this->redirect($auth_url);
		} 
		else
		{
			$cred = $client->authenticate($_GET['code']);
			/* write refresh token file */
			$token_json = json_encode($cred);
			$tokenFileLocation = __DIR__.'/../Data/token.json';
	    	$file = fopen($tokenFileLocation,"w");
	    	fwrite($file,$token_json);
	    	fclose($file);

			$session = $this->get('session');
			if (isset($_GET['code']))
			{
				$session->set('access_token', $client->getAccessToken());
			}
			$redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . '/analytic';
			$redirect_uri =   filter_var($redirect_uri, FILTER_SANITIZE_URL);
			return $this->redirect($redirect_uri);
			//return $this->redirectToRoute('analytic_homepage', array(), 301);
		}
    }


    public function uploadAction(Request $request){
    	return $this->render('GoogleAnalyticsApiBundle:Default:upload.html.twig', array(
        ));

    }
}

