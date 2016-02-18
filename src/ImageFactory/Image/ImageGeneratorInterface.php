<?php

namespace ImageFactory\Image;

interface ImageGeneratorInterface 
{	
	const COMPRESSION_PNG  = 'png_compression_level';
	const COMPRESSION_JPEG = 'jpeg_quality';
	const COMPRESSION_PNG_DEFAULT_LEVEL  = 7;
	const COMPRESSION_JPEG_DEFAULT_LEVEL = 75;
	
	/**
     * Set the compression type and value
     *
     * @param string $type
     * @param integer $value
     *
     * @throws InvalidCompressionTypeException
     *
     * @return AndroidBitmapGenerator
     */
	public function setCompression($type, $value);	
	
	/**
     * Set the image path
     *
     * @param string $path
     *
     * @return AndroidBitmapGenerator
     */
	public function setImagePath($path);
	
	/**
     * Set output path used to name each density file 
     *
     * @param string $path
     *
     * @return AndroidBitmapGenerator
     */
	public function setOutputPath($path);
	
	/**
     * Set the reference size necessary for defining the sizes of each density
     *
     * @param integer $width
     * @param integer $height
     *
     * @return AndroidBitmapGenerator
     */
	public function setReferenceSize($width, $height);
	

	/**
     * Generate the different densities files
     *
     * @param string $compressionType
     * @param integer $compressionValue
     */
	public function execute();
}

