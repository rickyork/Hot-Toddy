<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Framework
#//\\ @@@@  @@@@\\\\\|
#//\\\@@@@| @@@@\\\\\|
#//\\\ @@ |\\@@\\\\\\| https://github.com/rickyork/Hot-Toddy
#//\\\\  ||   \\\\\\\| © Copyright 2019 Richard York, All rights Reserved
#//\\\\  \\_   \\\\\\|
#//\\\\\        \\\\\| Use and redistribution are subject to the terms of the license.
#//\\\\\  ----  \@@@@| https://github.com/rickyork/Hot-Toddy/blob/master/License
#//@@@@@\       \@@@@|
#//@@@@@@\     \@@@@@|
#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\

class hFrameworkUpdate_102To103 extends hPlugin {

    public function hConstructor()
    {
        $this->hDatabase->query("DROP TABLE `hChat`");
        $this->hDatabase->query("DROP TABLE `hChatConversations`");
        $this->hDatabase->query("DROP TABLE `hChatUsers`");
        $this->hDatabase->query("DROP TABLE `hProductCurrencies`");
        $this->hDatabase->query("DROP TABLE `hProductDiscounts`");
        $this->hDatabase->query("DROP TABLE `hProductGroupModelNumbers`");
        $this->hDatabase->query("DROP TABLE `hProductGroups`");
        $this->hDatabase->query("DROP TABLE `hProductModelNumberParts`");
        $this->hDatabase->query("DROP TABLE `hProductModelNumbers`");
        $this->hDatabase->query("DROP TABLE `hProductOrderAdjustments`");
        $this->hDatabase->query("DROP TABLE `hProductOrderCart`");
        $this->hDatabase->query("DROP TABLE `hProductOrderComments`");
        $this->hDatabase->query("DROP TABLE `hProductOrderContact`");
        $this->hDatabase->query("DROP TABLE `hProductOrderContactTerritory`");
        $this->hDatabase->query("DROP TABLE `hProductOrderFollowUp`");
        $this->hDatabase->query("DROP TABLE `hProductOrderMail`");
        $this->hDatabase->query("DROP TABLE `hProductOrders`");
        $this->hDatabase->query("DROP TABLE `hProductOrderVariables`");
        $this->hDatabase->query("DROP TABLE `hProductPriceCategories`");
        $this->hDatabase->query("DROP TABLE `hProductPrices`");
        $this->hDatabase->query("DROP TABLE `hProducts`");
        $this->hDatabase->query("DROP TABLE `hProductSpecifications`");
        $this->hDatabase->query("DROP TABLE `hFileProducts`");
        $this->hDatabase->query("DROP TABLE `hFileMacICNS`");
    }
}

?>