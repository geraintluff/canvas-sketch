canvas-sketch
=============

Transform an image into a pencil-sketch, using the `<canvas>` element.

The implemenation is not exactly efficient, so when you check out the [demo page](http://geraintluff.github.io/canvas-sketch/), try starting with a small image (a couple of hundred pixels across).

## The algorithm

The principle is fairly simple.  At each point in the image, the hue is converted into an angle of shading (so blue and yellow are at right-angles to each other).  The saturation is converted into *consistency* of shading angles (so grey will be cross-hatched, grey-blue will be a bit rough-looking, and pure blue will be completely in line).  Brightness is obviously represented by density of lines.

The actual method is a bit of a hack - we create image-size pencil textures (using randomly drawn pencil lines with a specified thickness, length and density) for a few different RGB values.  The spacing of these values is determined by the "Level steps" control (so, 2 means that it will cover `(0, 0, 0)`, `(1, 0, 0)` and so on - 3 means it will cover `(0, 0, 0)`, `(0.5, 0, 0)` and so on).

Then, we simply composite these textures together (fading between them), and add some basic edge-detection to simulate outlines.

## Batch processing

I hacked together a batch-processing system as a PHP script.  It downloads one image at a time, processes it, and POSTs it back to the server.

The config is in `batch-animation.json`.  Note the `lineAlphaVariation` and `edgeAmountVariation` properties, which randomise their corresponding parameters, resulting in a more or less coarse image.

The goal is to replicate the effect of tracing video frames using pencil sketches, where each frame will not turn out completely consistently:

![GIF animation, from a video of a fruit bowl](100-coarse-random.gif)

## License

The code is available as "public domain", meaning that it is completely free to use, without any restrictions at all (including re-licensing under your own terms).  Read the full license [here](http://geraintluff.github.com/tv4/LICENSE.txt).

It's also available under an [MIT license](http://jsonary.com/LICENSE.txt).
