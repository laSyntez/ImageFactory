<?php

namespace ImageFactory\Android\Density;

use ImageFactory\Image\ImageGeneratorInterface;
use ImageFactory\Exception\InvalidCompressionTypeException;

class AndroidBitmapGenerator implements ImageGeneratorInterface
{
    /**
     * @var \Imagine\Image\ImagineInterface
     */
	protected $imagine;
	
	/**
     * @var string
     */
	protected $imagePath;	
	
	/**
     * @var string
     */
	protected $outputPath;	
	
	/**
     * @var integer
     */
	protected $referenceWidth;	
	
	/**
     * @var integer
     */
	protected $referenceHeight;
	
	/**
     * @var array
     */
	protected $densities = array();	
	
	/**
     * @var string
     */
	protected $compressionType = self::COMPRESSION_PNG;	
	
	/**
     * @var integer
     */
	protected $compressionValue = self::COMPRESSION_PNG_DEFAULT_LEVEL;	
	
	const DENSITY_MDPI    = 'mdpi';
	const DENSITY_HPI     = 'hdpi';
	const DENSITY_XHDPI   = 'xhdpi';
	const DENSITY_XXHDPI  = 'xxhdpi';
	const DENSITY_XXXHDPI = 'xxxhdpi';
	
	/**
     * Constructor.
     *
     * @param string $imagePath     
     * @param string $outputPath    
     * @param integer $referenceWidth
     * @param integer $referenceHeight
     */
	public function __construct($imagePath, $outputPath = null, $referenceWidth = -1, $referenceHeight = -1) 
	{
		$this->imagine = new \Imagine\Imagick\Imagine();	
		$this->setImagePath($imagePath); 
		$this->setOutputPath($outputPath); 
		$this->setReferenceSizes($referenceWidth, $referenceHeight);	
	}
	
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
	public function setCompression($type, $value)	
	{
		if (in_array($type, array(self::COMPRESSION_PNG, self::COMPRESSION_JPEG))) {
			$this-> compressionType = $type;			
			$this-> compressionValue = (int) $value;
		} else {
			throw new InvalidCompressionTypeException('A valid compression type must be defined');
		}
		
		return $this;
	}	
	
	/**
     * Set the image path
     *
     * @param string $path
     *
     * @return AndroidBitmapGenerator
     */
	public function setImagePath($path)	
	{
		if (is_string($path)) {
			$this->imagePath = $path;
		}
		
		$this->setReferenceSizes(-1, -1);
		
		return $this;
	}	
	
	/**
     * Set output path used to name each density file 
     *
     * @param string $path
     *
     * @return AndroidBitmapGenerator
     */
	public function setOutputPath($path)	
	{
		if (is_string($path)) {
			$this->outputPath = $path;
		}
		
		return $this;
	}	
	
	/**
     * Set the reference sizes necessary for defining the sizes of each density
     *
     * @param integer $width
     * @param integer $height
     *
     * @return AndroidBitmapGenerator
     */
	public function setReferenceSizes($width, $height)
	{
		if (0 < (int) $width && (int) $height > 0) {
			$this->referenceWidth = $width;
			$this->referenceHeight = $height;
		} else {
			$size = $this->imagine->open($this->imagePath)->getSize();
			$this->referenceWidth = $size->getWidth();			
			$this->referenceHeight = $size->getHeight();
		}	
		
		$this->setDensities();
		
		return $this;
	}
	
	/**
     * Set the sizes of each density based on the reference sizes 
     */
	protected function setDensities()
	{
		$this->densities = array(
			self::DENSITY_MDPI => array($this->referenceWidth/4, $this->referenceHeight/4),
			self::DENSITY_HPI => array($this->referenceWidth*3/8, $this->referenceHeight*3/8),
			self::DENSITY_XHDPI => array($this->referenceWidth/2, $this->referenceHeight/2),
			self::DENSITY_XXHDPI => array($this->referenceWidth*3/4, $this->referenceHeight*3/4),
			self::DENSITY_XXXHDPI => array($this->referenceWidth, $this->referenceHeight)
		);
	}
	
	/**
     * Generate the different densities files
     *
     * @param string $compressionType
     * @param integer $compressionValue
     */
	public function execute()
	{
		$outputPath = null != $this->outputPath ? $this->outputPath : $this->imagePath;
		$dotPos = stripos($outputPath, '.');
		$path = substr($outputPath, 0, $dotPos);
		$ext = substr($outputPath, $dotPos+1);
		
		foreach ($this->densities as $dens => $size) {
			$this->imagine->open($this->imagePath)
			     ->resize(new \Imagine\Image\Box($size[0], $size[1]), \Imagine\Image\ImageInterface::FILTER_POINT)
			     ->save($path.'-'.$dens.'.'.$ext,  array($this->compressionType => $this->compressionValue));
		}
	}
}

