<?php

namespace Timber;


interface ObjectFactoryInterface
{
	/**
	 * Get an instance of the queried object
	 *
	 * @param $query_object
	 * @return mixed
	 */
	public static function get( $query_object );
}
