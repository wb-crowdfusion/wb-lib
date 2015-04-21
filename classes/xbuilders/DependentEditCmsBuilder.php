<?php
/**
 * MediaEditCmsBuilder
 *
 * PHP version 5
 *
 * Crowd Fusion
 * Copyright (C) 2009-2010 Crowd Fusion, Inc.
 * http://www.crowdfusion.com/
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted under the terms of the BSD License.
 *
 * @package     CrowdFusion
 * @copyright   2009-2010 Crowd Fusion Inc.
 * @license     http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version     $Id: DependentEditCmsBuilder.php 603 2011-07-20 04:10:33Z 12dnetworks $
 */

/**
 * MediaEditCmsBuilder
 *
 * @package     CrowdFusion
 */
class DependentEditCmsBuilder extends EditCmsBuilder
{
    protected function _buildWidgetOptions($schemafield,$attributes) {

        $opt = parent::_buildWidgetOptions($schemafield,$attributes);

        if(!empty($attributes['depends-on']))
            $opt[] = "			DependsOn: '".($attributes['depends-on'])."'";

        if(!empty($attributes['provider-for']))
            $opt[] = "			ProviderFor: '".($attributes['provider-for'])."'";

        return $opt;
    }

}
