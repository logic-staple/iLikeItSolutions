<?php

namespace iLikeItSolutions\GoogleAnalyticsApiBundle\Service;

use Google_Client;
use Google_Service_AnalyticsReporting;
use Google_Service_AnalyticsReporting_DateRange;
use Google_Service_AnalyticsReporting_OrderBy;
use Google_Service_AnalyticsReporting_GetReportsRequest;
use Google_Service_AnalyticsReporting_ReportData;
use Google_Service_AnalyticsReporting_Metric;
use Google_Service_AnalyticsReporting_Dimension;
use Google_Service_AnalyticsReporting_ReportRequest;
use Symfony\Component\Config\Definition\Exception\Exception;

/**
 * Class GoogleAnalyticsService
 *
 */
class GoogleAnalyticsService {

    /**
     * @var Google_Client
     */
    private $client;
    /**
     * @var Google_Service_AnalyticsReporting
     */
    private $analytics;

    /**
     * construct
     */
    //public function __construct($keyFileLocation) {
    public function __construct() {
        
        $keyFileLocation = __DIR__.'/../Data/client_secrets.json';

        if (!file_exists($keyFileLocation)) {
            throw new Exception("can't find file key location defined by analytic.google_analytics_json_key parameter, ex : ../data/client_secrets.json");
        }
        try{
            $this->client = new Google_Client();
        }catch(Exception $e){
            echo "Exception: ".$e->getMessage();
        }
        //$this->client->setApplicationName("GoogleAnalytics");
        $this->client->setAccessType("offline");        // offline access
        $this->client->setIncludeGrantedScopes(true);   // incremental auth
        //$this->client->addScope(Google_Service_Analytics::ANALYTICS_READONLY);
        $this->client->setScopes(['https://www.googleapis.com/auth/analytics.readonly']);
        $this->client->setAuthConfig($keyFileLocation);

        $this->analytics = new Google_Service_AnalyticsReporting($this->client);

    }

    /**
     * @return Google_Service_AnalyticsReporting
     */
    public function getAnalytics() {

        return $this->analytics;

    }

    /**
     * @return Google_Client
     */
    public function getClient() {

        return $this->client;

    }

    /**
     * @param $viewId
     * @param $dateStart
     * @param $dateEnd
     * @param $expression
     * @return mixed
     */
    private function getDataDateRange($viewId,$dateStart,$dateEnd,$expression) {

        // Create the DateRange object
        $dateRange = new Google_Service_AnalyticsReporting_DateRange();
        $dateRange->setStartDate($dateStart);
        $dateRange->setEndDate($dateEnd);

        // Create the Metrics object
        $sessions = new Google_Service_AnalyticsReporting_Metric();
        $sessions->setExpression("ga:$expression");
        $sessions->setAlias("sessions");

        // Create the ReportRequest object
        $request = new Google_Service_AnalyticsReporting_ReportRequest();
        $request->setViewId($viewId);
        $request->setDateRanges($dateRange);
        $request->setMetrics([$sessions]);

        $body = new Google_Service_AnalyticsReporting_GetReportsRequest();
        $body->setReportRequests([$request]);

        $report = $this->analytics->reports->batchGet($body);

        $result = $report->getReports()[0]
            ->getData()
            ->getTotals()[0]
            ->getValues()[0]
        ;

        return $result;

    }

    /**
     * @param $viewId
     * @param $dateStart
     * @param $dateEnd
     * @return mixed
     */
    public function getSessionsDateRange($viewId,$dateStart,$dateEnd) {
        return $this->getDataDateRange($viewId,$dateStart,$dateEnd,'sessions');
    }

    /**
     * @param $viewId
     * @param $dateStart
     * @param $dateEnd
     * @return mixed
     */
    public function getBounceRateDateRange($viewId,$dateStart,$dateEnd) {
        return $this->getDataDateRange($viewId,$dateStart,$dateEnd,'bounceRate');
    }

    /**
     * @param $viewId
     * @param $dateStart
     * @param $dateEnd
     * @return mixed
     */
    public function getAvgTimeOnPageDateRange($viewId,$dateStart,$dateEnd) {
        return $this->getDataDateRange($viewId,$dateStart,$dateEnd,'avgTimeOnPage');
    }

    /**
     * @param $viewId
     * @param $dateStart
     * @param $dateEnd
     * @return mixed
     */
    public function getPageviewsPerSessionDateRange($viewId,$dateStart,$dateEnd) {
        return $this->getDataDateRange($viewId,$dateStart,$dateEnd,'pageviewsPerSession');
    }

    /**
     * @param $viewId
     * @param $dateStart
     * @param $dateEnd
     * @return mixed
     */
    public function getPercentNewVisitsDateRange($viewId,$dateStart,$dateEnd) {
        return $this->getDataDateRange($viewId,$dateStart,$dateEnd,'percentNewVisits');
    }

    /**
     * @param $viewId
     * @param $dateStart
     * @param $dateEnd
     * @return mixed
     */
    public function getPageViewsDateRange($viewId,$dateStart,$dateEnd) {
        return $this->getDataDateRange($viewId,$dateStart,$dateEnd,'pageviews');
    }

    /**
     * @param $viewId
     * @param $dateStart
     * @param $dateEnd
     * @return mixed
     */
    public function getAvgPageLoadTimeDateRange($viewId,$dateStart,$dateEnd) {
        return $this->getDataDateRange($viewId,$dateStart,$dateEnd,'avgPageLoadTime');
    }

    /* Get Trafiic data by source and country */
    public function getTraffic($viewId,$dateStart,$dateEnd, $metr = "ga:sessions", $dim = "ga:source", $limit){
        //https://github.com/google/google-api-php-client-services/blob/master/src/Google/Service/AnalyticsReporting/Dimension.php
        // Create the DateRange object
        //default take 23 May to 22 June ------ current date is 23 June
        $dateRange = new Google_Service_AnalyticsReporting_DateRange();
        $dateRange->setStartDate($dateStart);
        $dateRange->setEndDate($dateEnd);
        // Create the Metrics object
        $metrics = new Google_Service_AnalyticsReporting_Metric();
        $metrics->setExpression($metr);
        $metrics->setAlias("sessions");

        // Create the dimension object
        $dimension = new Google_Service_AnalyticsReporting_Dimension();
        $dimension->setName($dim);

        $order_by = new Google_Service_AnalyticsReporting_OrderBy();
        $order_by->setFieldName('sessions');
        $order_by->setSortOrder('DESCENDING');

        // Create the ReportRequest object
        $request = new Google_Service_AnalyticsReporting_ReportRequest();
        $request->setViewId($viewId);
        $request->setDateRanges($dateRange);
        $request->setMetrics([$metrics]);
        $request->setDimensions([$dimension]);
        $request->setOrderBys([$order_by]);
        if($limit != "All"){
            $request->setPageSize($limit); // set page size
        }
        $request->setIncludeEmptyRows(false);
        $request->setSamplingLevel("SMALL");
        $body = new Google_Service_AnalyticsReporting_GetReportsRequest();
        $body->setReportRequests([$request]);

        $report = $this->analytics->reports->batchGet($body);

        $result = $report->getReports()[0]->getData()->getRows();
        $finalResult = array();
        if(!empty($result)){
            foreach ($result as $key => $value) {
                $finalResult[] = array(
                                        'source' => isset($value['dimensions'][0])?$value['dimensions'][0]:"N/A",
                                        'value' => isset($value['metrics'][0]['values'][0])?$value['metrics'][0]['values'][0]:"N/A"
                                        );
            }
        }
        //echo "<pre>";print_r($report);exit;
        return $finalResult;
    }

    /* Get Pages data visits and percentage */
    public function getPagesVisits($viewId,$dateStart,$dateEnd){
        //https://github.com/google/google-api-php-client-services/blob/master/src/Google/Service/AnalyticsReporting/Dimension.php
        // Create the DateRange object
        //default take 23 May to 22 June ------ current date is 23 June
        $dateRange = new Google_Service_AnalyticsReporting_DateRange();
        $dateRange->setStartDate($dateStart);
        $dateRange->setEndDate($dateEnd);
        
        // Create the Metrics object
        $metrics1 = new Google_Service_AnalyticsReporting_Metric();
        $metrics1->setExpression("ga:pageviews");
        $metrics1->setAlias("pageviews");

        /*$metrics2 = new Google_Service_AnalyticsReporting_Metric();
        $metrics2->setExpression("ga:entranceRate");
        $metrics2->setAlias("percentage");*/

        // Create the dimension object
        $dimension = new Google_Service_AnalyticsReporting_Dimension();
        $dimension->setName('ga:pagePath');

        $order_by = new Google_Service_AnalyticsReporting_OrderBy();
        $order_by->setFieldName('pageviews');
        $order_by->setSortOrder('DESCENDING');

        // Create the ReportRequest object
        $request = new Google_Service_AnalyticsReporting_ReportRequest();
        $request->setViewId($viewId);
        $request->setDateRanges($dateRange);
        $request->setMetrics([$metrics1]);
        $request->setDimensions([$dimension]);
        $request->setOrderBys([$order_by]);
        $request->setPageSize(5); // set page size
        $request->setIncludeEmptyRows(false);
        $request->setSamplingLevel("SMALL");
        $body = new Google_Service_AnalyticsReporting_GetReportsRequest();
        $body->setReportRequests([$request]);

        $report = $this->analytics->reports->batchGet($body);

        $result = $report->getReports()[0]->getData()->getRows();
        //echo "<pre>";print_r($result);exit;
        $finalResult = array();
        if(!empty($result)){
            foreach ($result as $key => $value) {
                $finalResult[] = array(
                                        'path' => isset($value['dimensions'][0])?$value['dimensions'][0]:"N/A",
                                        'pageviews' => isset($value['metrics'][0]['values'][0])?$value['metrics'][0]['values'][0]:"N/A"
                                        );
            }
        }
        
        return $finalResult;
    }



    /* Get Pages data visits and percentage */
    public function getPagesSessionWeekly($viewId,$dateStart,$dateEnd){
        // Metric 'ga:sessions,ga:pageviews',  Dimension 'ga:week'
        $dateRange = new Google_Service_AnalyticsReporting_DateRange();
        $dateRange->setStartDate($dateStart);
        $dateRange->setEndDate($dateEnd);
        
        // Create the Metrics object
        $metrics1 = new Google_Service_AnalyticsReporting_Metric();
        $metrics1->setExpression("ga:sessions");
        //$metrics1->setAlias("sessions");

        $metrics2 = new Google_Service_AnalyticsReporting_Metric();
        $metrics2->setExpression("ga:pageviews");
        //$metrics2->setAlias("pageviews");

        // Create the dimension object
        $dimension = new Google_Service_AnalyticsReporting_Dimension();
        $dimension->setName('ga:dayOfWeek');

        $order_by = new Google_Service_AnalyticsReporting_OrderBy();
        $order_by->setFieldName('ga:dayOfWeek');
        $order_by->setSortOrder('ASCENDING');
        
        // Create the ReportRequest object
        $request = new Google_Service_AnalyticsReporting_ReportRequest();
        $request->setViewId($viewId);
        $request->setDateRanges($dateRange);
        $request->setMetrics([$metrics1, $metrics2]);
        //$request->setMetrics([$metrics2]);
        $request->setDimensions([$dimension]);
        $request->setOrderBys([$order_by]);
        //$request->setPageSize(5); // set page size
        //$request->setIncludeEmptyRows(false);
        //$request->setSamplingLevel("SMALL");
        $body = new Google_Service_AnalyticsReporting_GetReportsRequest();
        $body->setReportRequests([$request]);

        $report = $this->analytics->reports->batchGet($body);

        $result = $report->getReports()[0]->getData()->getRows();
        $finalResult = array();
        if(!empty($result)){
            foreach ($result as $key => $value) {
                $finalResult['sessions'][] = $value['metrics'][0]['values'][0];
                $finalResult['pageviews'][] = $value['metrics'][0]['values'][1];
            }
        }
        return $finalResult;
    }


}
