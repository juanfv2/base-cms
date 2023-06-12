<?php

namespace Juanfv2\BaseCms\Traits;

use App\Models\Misc\TrackingJob;

trait ListenerTracking
{
    public function queueName()
    {
        $arg_list = func_get_args();
        $uid = auth()->user()->id;
        $time = _sanitize(now()->format('Y-m-d-H:i:s.u'));
        $rCountry = request()->get('rCountry', request()->headers->get('r-country', '.l.'));
        $queue = _sanitize(implode('--', $arg_list));

        return "{$rCountry}__{$queue}__{$uid}__{$time}";
    }

    /**
     * Cambia estado a STATUS_PENDING
     *
     * @return void
     */
    public function trackingPending($db, $nameQ, $userId)
    {
        $jobTracking = new TrackingJob();
        $jobTracking->setConnection(config('base-cms.default_prefix').$db);
        $jobTracking->queue = $nameQ;
        $jobTracking->user_id = $userId;

        $jobTracking->sPending();
    }

    /**
     * Cambia estado a STATUS_INITIATED
     *
     * @return void
     */
    private function trackingInit($notify = true)
    {
        $trackingJob = TrackingJob::whereQueue($this->event->data->cQueue)->first();
        if ($trackingJob) {
            $trackingJob->sInitiated($notify);
        }
    }

    /**
     * Cambia estado a STATUS_FINISHED_SUCCESS
     *
     * @return void
     */
    private function trackingSuccess($link = '', $notify = true)
    {
        $trackingJob = TrackingJob::whereQueue($this->event->data->cQueue)->first();
        if ($trackingJob) {
            if ($link != 'no-info') {
                $trackingJob->link = $link;
            }
            $trackingJob->sFinishedSuccess($notify);
        }
    }

    /**
     * Cambia estado a STATUS_FINISHED_ERROR
     *
     * @return void
     */
    public function trackingError()
    {
        $trackingJob = TrackingJob::whereQueue($this->event->data->cQueue)->first();
        if ($trackingJob) {
            $trackingJob->sFinishedError();
        }
    }
}
