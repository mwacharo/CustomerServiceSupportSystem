<?php

namespace App\Services;

use Atymic\Twitter\Facade\Twitter;
use Illuminate\Support\Facades\Log;
use Thujohn\Twitter\Twitter as TwitterTwitter;

class TwitterBotService
{
    public function postMarketingTweets(): void
    {
        $messages = [
            "💼 Try our HRM system today! Smart HR for smart teams. #HRTech",
            "🛍️ Our POS + Omnichannel System simplifies sales. Try it now. #RetailTech",
            "🚚 Logistics made easy with our courier solution. #Logistics",
            "🏠 Manage rentals effortlessly. Discover our rental system. #PropTech"
        ];

        foreach ($messages as $msg) {
            try {
                Twitter::postTweet(['status' => $msg, 'format' => 'json']);
                Log::info("Tweet posted: $msg");
            } catch (\Exception $e) {
                Log::error("Twitter post error: " . $e->getMessage());
            }
        }
    }

    public function engageWithKeywords(array $keywords = []): void
    {
        if (empty($keywords)) {
            $keywords = ['logistics', 'POS system', 'rental management', 'HR software', 'CRM solution'];
        }

        foreach ($keywords as $keyword) {
            try {
                $results = Twitter::getSearch([
                    'q' => $keyword,
                    'count' => 5,
                    'result_type' => 'recent',
                    'lang' => 'en',
                ]);

                foreach ($results->statuses as $tweet) {
                    $tweetId = $tweet->id_str;
                    $userId = $tweet->user->id_str;

                    Twitter::postFavorite(['id' => $tweetId]);
                    Twitter::postRt($tweetId);
                    Twitter::postFriendshipsCreate(['user_id' => $userId]);

                    Log::info("Engaged with @{$tweet->user->screen_name} on '$keyword'");
                }

            } catch (\Exception $e) {
                Log::error("Engagement error for '$keyword': " . $e->getMessage());
            }
        }
    }

    public function runFullCampaign(): void
    {
        $this->postMarketingTweets();
        $this->engageWithKeywords();
    }
}
